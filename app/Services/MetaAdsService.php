<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MetaAdsService
{
    public function getYesterdaySpend(): array
    {
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
            throw new \RuntimeException(
                'Missing Meta API configuration. Check config/meta.php and .env.'
            );
        }

        $results = null;

        foreach ($accountIds as $accountId) {
            $accountId = trim((string) $accountId);

            if ($accountId === '') {
                continue;
            }

            $response = Http::get(
                "https://graph.facebook.com/{$version}/act_{$accountId}/insights",
                [
                    'fields' => 'campaign_id,campaign_name,spend,date_start,date_stop',
                    'level' => 'campaign',
                    'date_preset' => 'yesterday',
                    'access_token' => $token,
                ]
            );

            $response->throw();
            $accountResults = $response->json();

            if (! is_array($accountResults)) {
                throw new \RuntimeException('Meta API returned an invalid response.');
            }

            if ($results === null) {
                $results = $accountResults;

                continue;
            }

            $results['data'] = array_merge(
                $results['data'] ?? [],
                $accountResults['data'] ?? []
            );
        }

        return $results ?? ['data' => []];
    }
}
