<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class InspectMetaAdsTest extends TestCase
{
    public function test_it_prints_all_raw_api_pages_as_json_for_the_requested_date(): void
    {
        config([
            'meta.api_version' => 'v24.0',
            'meta.ad_account_id' => '123456789',
            'meta.ad_account_ids' => [],
            'meta.access_token' => 'test-token',
        ]);

        Http::fake([
            'graph.facebook.com/v24.0/act_123456789/insights*' => Http::sequence()
                ->push([
                    'data' => [[
                        'campaign_id' => 'campaign-1',
                        'campaign_name' => 'Gagan 29OCT',
                        'ad_id' => 'ad-1',
                        'ad_name' => 'First ad',
                        'spend' => '10.00',
                        'date_start' => '2026-10-29',
                        'date_stop' => '2026-10-29',
                    ]],
                    'paging' => [
                        'next' => 'https://graph.facebook.com/next',
                        'cursors' => ['after' => 'cursor-2'],
                    ],
                ])
                ->push([
                    'data' => [[
                        'campaign_id' => 'campaign-1',
                        'campaign_name' => 'Gagan 29OCT',
                        'ad_id' => 'ad-2',
                        'ad_name' => 'Second ad',
                        'spend' => '20.00',
                        'date_start' => '2026-10-29',
                        'date_stop' => '2026-10-29',
                    ]],
                ]),
        ]);

        $this->artisan('app:inspect-meta-ads 2026-10-29')
            ->expectsOutputToContain('"date": "2026-10-29"')
            ->assertExitCode(0);

        Http::assertSentCount(2);
        Http::assertSent(fn ($request): bool => $request['time_range'] === '{"since":"2026-10-29","until":"2026-10-29"}'
            && $request['level'] === 'ad'
        );
    }

    public function test_it_rejects_an_invalid_date(): void
    {
        config([
            'meta.api_version' => 'v24.0',
            'meta.ad_account_id' => '123456789',
            'meta.ad_account_ids' => [],
            'meta.access_token' => 'test-token',
        ]);

        Http::fake();

        $this->artisan('app:inspect-meta-ads 2026-02-30')
            ->expectsOutputToContain('Date must be a valid YYYY-MM-DD value.')
            ->assertExitCode(1);

        Http::assertNothingSent();
    }

    public function test_it_inspects_each_configured_account(): void
    {
        config([
            'meta.api_version' => 'v24.0',
            'meta.ad_account_ids' => ['111111111', '222222222'],
            'meta.access_token' => 'test-token',
        ]);

        Http::fake(function ($request) {
            $path = (string) parse_url($request->url(), PHP_URL_PATH);

            if (str_ends_with($path, '/act_111111111/insights')) {
                return Http::response(['data' => [['ad_id' => 'ad-1']]]);
            }

            if (str_ends_with($path, '/act_222222222/insights')) {
                return Http::response(['data' => [['ad_id' => 'ad-2']]]);
            }

            return Http::response([], 404);
        });

        $this->artisan('app:inspect-meta-ads 2026-10-29')
            ->expectsOutputToContain('"record_count": 2')
            ->assertExitCode(0);

        Http::assertSentCount(2);
        Http::assertSent(fn ($request): bool => str_ends_with(
            (string) parse_url($request->url(), PHP_URL_PATH),
            '/act_111111111/insights'
        )
        );
        Http::assertSent(fn ($request): bool => str_ends_with(
            (string) parse_url($request->url(), PHP_URL_PATH),
            '/act_222222222/insights'
        )
        );
    }
}
