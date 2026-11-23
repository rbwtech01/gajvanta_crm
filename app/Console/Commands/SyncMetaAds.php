<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class SyncMetaAds extends Command
{
    protected $signature = 'app:sync-meta-ads';

    protected $description = 'Sync yesterday Meta Ads data into CRM';

    public function handle(): int
    {
        $this->info('Meta Ads sync started.');

        try {
            $version = config('meta.api_version');
            $accountIds = config('meta.ad_account_ids', []);
            $accountIds = is_array($accountIds) ? $accountIds : [];

            if ($accountIds === []) {
                $accountIds = [config('meta.ad_account_id')];
            }

            $accountIds = array_values(array_unique(array_filter(
                array_map(fn ($accountId) => trim((string) $accountId), $accountIds)
            )));
            $token = config('meta.access_token');

            if (! $version || $accountIds === [] || ! $token) {
                throw new RuntimeException(
                    'Missing Meta API configuration. Check config/meta.php and .env.'
                );
            }

            // Add this line before the API request loop.
            $reportDate = now('Asia/Kolkata')
                ->subDay()
                ->toDateString();

            $baseUrl = "https://graph.facebook.com/{$version}";

            $agents = User::role('Agent')
                ->where('is_active', true)
                ->get(['id', 'name']);

            if ($agents->isEmpty()) {
                throw new RuntimeException(
                    'No active users with the Agent role were found.'
                );
            }

            // Map normalized first names to their matching agents.
            $agentsByFirstName = [];

            foreach ($agents as $agent) {
                $firstName = $this->normalizeFirstName($agent->name);

                if ($firstName !== '') {
                    $agentsByFirstName[$firstName][] = $agent;
                }
            }

            $after = null;
            $pageCount = 0;
            $totalApiRecords = 0;
            $campaignsByDate = [];
            $processed = 0;
            $unmatched = 0;
            $failed = 0;

            foreach ($accountIds as $accountId) {
                $accountId = trim((string) $accountId);

                if ($accountId === '') {
                    continue;
                }

                $this->info("Fetching Meta Ads account: {$accountId}");
                $after = null;

                do {
                    $params = [
                        'fields' => 'campaign_id,campaign_name,ad_id,ad_name,spend,date_start,date_stop',
                        'level' => 'ad',
                        'time_range' => json_encode([
                            'since' => $reportDate,
                            'until' => $reportDate,
                        ]),
                        'limit' => 100,
                    ];

                    if ($after !== null) {
                        $params['after'] = $after;
                    }

                    $response = Http::withToken($token)
                        ->timeout(60)
                        ->get(
                            "{$baseUrl}/act_{$accountId}/insights",
                            $params
                        )
                        ->throw()
                        ->json();

                    if (! is_array($response)) {
                        throw new RuntimeException(
                            'Meta API returned an invalid response.'
                        );
                    }

                    $pageCount++;
                    $campaigns = $response['data'] ?? [];
                    $pageRecords = count($campaigns);
                    $totalApiRecords += $pageRecords;

                    $hasNextPage = ! empty($response['paging']['next']);
                    $nextCursor = $response['paging']['cursors']['after'] ?? null;

                    $this->info("API page: {$pageCount}");
                    $this->info("Records on page: {$pageRecords}");
                    $this->info("Total API records fetched: {$totalApiRecords}");
                    $this->info('Has next page: '.($hasNextPage ? 'YES' : 'NO'));

                    foreach ($campaigns as $campaign) {
                        $campaignId = $campaign['campaign_id'] ?? null;
                        $campaignName = trim($campaign['campaign_name'] ?? '');
                        $adId = $campaign['ad_id'] ?? null;
                        $adName = trim($campaign['ad_name'] ?? '');
                        $spend = $campaign['spend'] ?? null;
                        $campaignDate = $campaign['date_start'] ?? null;

                        if (
                            ! $campaignId ||
                            $campaignName === '' ||
                            ! $adId ||
                            ! is_numeric($spend) ||
                            ! $campaignDate
                        ) {
                            $unmatched++;

                            $this->warn('Skipped incomplete ad record.');

                            Log::warning('Incomplete Meta ad record', [
                                'campaign_id' => $campaignId,
                                'campaign_name' => $campaignName,
                                'ad_id' => $adId,
                                'report_date' => $campaignDate,
                            ]);

                            continue;
                        }

                        $campaignKey = $campaignId.'|'.$campaignDate;

                        if (! isset($campaignsByDate[$campaignKey])) {
                            $campaignsByDate[$campaignKey] = [
                                'campaign_id' => (string) $campaignId,
                                'campaign_name' => $campaignName,
                                'report_date' => $campaignDate,
                                'ads' => [],
                            ];
                        }

                        $campaignsByDate[$campaignKey]['ads'][(string) $adId] = [
                            'ad_id' => (string) $adId,
                            'ad_name' => $adName,
                            'spend' => (float) $spend,
                        ];
                    }

                    // Follow the cursor only if Meta supplies a next page.
                    if ($hasNextPage && $nextCursor !== null) {
                        $after = $nextCursor;
                    } elseif ($hasNextPage) {
                        throw new RuntimeException(
                            'Meta indicated another page but returned no cursor.'
                        );
                    } else {
                        $after = null;
                    }
                } while ($after !== null);
            }

            foreach ($campaignsByDate as $campaign) {
                $campaignId = $campaign['campaign_id'];
                $campaignName = $campaign['campaign_name'];
                $campaignDate = $campaign['report_date'];

                $firstName = $this->normalizeFirstName($campaignName);
                $matches = $agentsByFirstName[$firstName] ?? [];

                if (count($matches) !== 1) {
                    $unmatched++;

                    $reason = count($matches) === 0
                        ? 'No active Agent matched'
                        : 'Multiple active Agents matched';

                    $this->warn("{$reason}: {$campaignName}");

                    Log::warning('Meta Ads agent matching failed', [
                        'campaign_id' => $campaignId,
                        'campaign_name' => $campaignName,
                        'normalized_first_name' => $firstName,
                        'report_date' => $campaignDate,
                        'reason' => $reason,
                    ]);

                    continue;
                }

                $agent = $matches[0];
                $ads = $campaign['ads'];

                try {
                    $budget = $this->getCampaignBudget(
                        $baseUrl,
                        $campaignId,
                        $token
                    );

                    if ($budget === null) {
                        $this->warn(
                            "Budget unavailable for {$campaignName}; spend will sync, ".
                                'but budget will remain unavailable.'
                        );
                    }

                    DB::transaction(function () use (
                        $campaignId,
                        $campaignDate,
                        $campaignName,
                        $agent,
                        $ads,
                        $budget
                    ): void {
                        DB::table('meta_ad_reports')
                            ->where('campaign_id', $campaignId)
                            ->where('report_date', $campaignDate)
                            ->whereNull('ad_id')
                            ->delete();

                        $budgetAdId = (string) array_key_first($ads);

                        foreach ($ads as $ad) {
                            $values = [
                                'agent_id' => $agent->id,
                                'campaign_id' => $campaignId,
                                'campaign_name' => $campaignName,
                                'ad_name' => $ad['ad_name'],
                                'amount_spent' => $ad['spend'],
                                'updated_at' => now(),
                                'created_at' => now(),
                            ];

                            if ((string) $ad['ad_id'] === $budgetAdId) {
                                if ($budget !== null) {
                                    $values['budget'] = $budget['amount'];
                                    $values['budget_type'] = $budget['type'];
                                }
                            } else {
                                $values['budget'] = 0;
                                $values['budget_type'] = null;
                            }

                            DB::table('meta_ad_reports')->updateOrInsert(
                                [
                                    'ad_id' => $ad['ad_id'],
                                    'report_date' => $campaignDate,
                                ],
                                $values
                            );
                        }
                    });

                    $processed += count($ads);

                    $this->line(
                        "{$campaignName}: Agent {$agent->name}, ".
                            count($ads).' ads processed'
                    );
                } catch (Throwable $e) {
                    $failed++;

                    $this->error(
                        "Failed campaign {$campaignName}: {$e->getMessage()}"
                    );

                    Log::error('Meta campaign processing failed', [
                        'campaign_id' => $campaignId,
                        'campaign_name' => $campaignName,
                        'report_date' => $campaignDate,
                        'message' => $e->getMessage(),
                    ]);
                }
            }

            $this->newLine();
            $this->info("Total API pages: {$pageCount}");
            $this->info("Total API records fetched: {$totalApiRecords}");
            $this->info("Processed: {$processed}");
            $this->info("Unmatched/incomplete: {$unmatched}");
            $this->info("Failed: {$failed}");
            $this->info('Meta Ads sync finished.');

            return self::SUCCESS;
        } catch (Throwable $e) {
            Log::error('Meta Ads sync failed', [
                'message' => $e->getMessage(),
            ]);

            $this->error(
                'Meta Ads sync failed. Check storage/logs/laravel.log.'
            );

            return self::FAILURE;
        }
    }

    /**
     * Normalize a name and return its first word.
     * Example: "Gagan-ak- campaign" becomes "gagan".
     */
    private function normalizeFirstName(string $name): string
    {
        $name = mb_strtolower(trim($name));

        $name = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $name);

        $words = preg_split('/\s+/u', trim($name));

        return $words[0] ?? '';
    }

    /**
     * @return array{amount: float, type: 'daily'|'lifetime'}|null
     */
    private function getCampaignBudget(
        string $baseUrl,
        string $campaignId,
        string $token
    ): ?array {
        try {
            $data = Http::withToken($token)
                ->timeout(30)
                ->get("{$baseUrl}/{$campaignId}", [
                    'fields' => 'daily_budget,lifetime_budget',
                ])
                ->throw()
                ->json();

            if (isset($data['daily_budget']) && (float) $data['daily_budget'] > 0) {
                return [
                    'amount' => ((float) $data['daily_budget']) / 100,
                    'type' => 'daily',
                ];
            }

            if (isset($data['lifetime_budget']) && (float) $data['lifetime_budget'] > 0) {
                return [
                    'amount' => ((float) $data['lifetime_budget']) / 100,
                    'type' => 'lifetime',
                ];
            }

            $dailyTotal = 0.0;
            $lifetimeTotal = 0.0;
            $hasDailyBudget = false;
            $hasLifetimeBudget = false;

            $adSetResponse = Http::withToken($token)
                ->timeout(30)
                ->get("{$baseUrl}/{$campaignId}/adsets", [
                    'fields' => 'daily_budget,lifetime_budget',
                    'limit' => 100,
                ])
                ->throw()
                ->json();
            $adSets = $adSetResponse['data'] ?? [];
            $nextPage = $adSetResponse['paging']['next'] ?? null;

            while (true) {
                foreach ($adSets as $adSet) {
                    if (isset($adSet['daily_budget']) && (float) $adSet['daily_budget'] > 0) {
                        $dailyTotal += ((float) $adSet['daily_budget']) / 100;
                        $hasDailyBudget = true;
                    }

                    if (isset($adSet['lifetime_budget']) && (float) $adSet['lifetime_budget'] > 0) {
                        $lifetimeTotal += ((float) $adSet['lifetime_budget']) / 100;
                        $hasLifetimeBudget = true;
                    }
                }

                if ($nextPage === null) {
                    break;
                }

                $adSetResponse = Http::withToken($token)
                    ->timeout(30)
                    ->get($nextPage)
                    ->throw()
                    ->json();

                $adSets = $adSetResponse['data'] ?? [];
                $nextPage = $adSetResponse['paging']['next'] ?? null;
            }

            if ($hasDailyBudget && $hasLifetimeBudget) {
                Log::warning('Meta campaign has mixed daily and lifetime ad-set budgets.', [
                    'campaign_id' => $campaignId,
                ]);
            }

            if ($hasDailyBudget) {
                return [
                    'amount' => $dailyTotal,
                    'type' => 'daily',
                ];
            }

            if ($hasLifetimeBudget) {
                return [
                    'amount' => $lifetimeTotal,
                    'type' => 'lifetime',
                ];
            }

            Log::warning('Meta campaign response contained no budget fields.', [
                'campaign_id' => $campaignId,
            ]);

            return null;
        } catch (Throwable $e) {
            Log::warning('Could not fetch Meta daily budget', [
                'campaign_id' => $campaignId,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
