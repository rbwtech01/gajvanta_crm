<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class InspectMetaAds extends Command
{
    protected $signature = 'app:inspect-meta-ads {date : Date to inspect in YYYY-MM-DD format}';

    protected $description = 'Print raw paginated Meta ad insights as JSON without saving them';

    public function handle(): int
    {
        try {
            $date = (string) $this->argument('date');

            if (
                ! preg_match('/\A[0-9]{4}-[0-9]{2}-[0-9]{2}\z/D', $date)
                || ! checkdate(
                    (int) substr($date, 5, 2),
                    (int) substr($date, 8, 2),
                    (int) substr($date, 0, 4)
                )
            ) {
                throw new RuntimeException('Date must be a valid YYYY-MM-DD value.');
            }

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

            $baseUrl = "https://graph.facebook.com/{$version}";
            $pages = [];
            $totalRecords = 0;

            foreach ($accountIds as $accountId) {
                $accountId = trim((string) $accountId);

                if ($accountId === '') {
                    continue;
                }

                $after = null;

                do {
                    $params = [
                        'fields' => 'campaign_id,campaign_name,ad_id,ad_name,spend,date_start,date_stop',
                        'level' => 'ad',
                        'time_range' => json_encode([
                            'since' => $date,
                            'until' => $date,
                        ], JSON_THROW_ON_ERROR),
                        'limit' => 100,
                    ];

                    if ($after !== null) {
                        $params['after'] = $after;
                    }

                    $response = Http::withToken($token)
                        ->timeout(60)
                        ->get("{$baseUrl}/act_{$accountId}/insights", $params)
                        ->throw()
                        ->json();

                    if (! is_array($response) || ! is_array($response['data'] ?? null)) {
                        throw new RuntimeException(
                            'Meta API returned an invalid insights response.'
                        );
                    }

                    $response['account_id'] = $accountId;
                    $pages[] = $response;
                    $totalRecords += count($response['data']);

                    $hasNextPage = ! empty($response['paging']['next']);
                    $nextCursor = $response['paging']['cursors']['after'] ?? null;

                    if ($hasNextPage && $nextCursor === null) {
                        throw new RuntimeException(
                            'Meta indicated another page but returned no cursor.'
                        );
                    }

                    $after = $hasNextPage ? $nextCursor : null;
                } while ($after !== null);
            }

            $this->line(json_encode([
                'date' => $date,
                'account_ids' => array_values(array_filter($accountIds)),
                'level' => 'ad',
                'page_count' => count($pages),
                'record_count' => $totalRecords,
                'pages' => $pages,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

            return self::SUCCESS;
        } catch (Throwable $exception) {
            Log::error('Meta Ads inspection failed', [
                'message' => $exception->getMessage(),
            ]);

            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}
