<?php

namespace Tests\Feature;

use App\Services\MetaAdsService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MetaAdsServiceTest extends TestCase
{
    public function test_it_combines_yesterday_spend_from_all_configured_accounts(): void
    {
        config([
            'meta.api_version' => 'v24.0',
            'meta.ad_account_ids' => ['111111111', '222222222'],
            'meta.access_token' => 'test-token',
        ]);

        Http::fake(function ($request) {
            $path = (string) parse_url($request->url(), PHP_URL_PATH);

            if (str_ends_with($path, '/act_111111111/insights')) {
                return Http::response(['data' => [['campaign_id' => 'campaign-1']]]);
            }

            if (str_ends_with($path, '/act_222222222/insights')) {
                return Http::response(['data' => [['campaign_id' => 'campaign-2']]]);
            }

            return Http::response([], 404);
        });

        $result = app(MetaAdsService::class)->getYesterdaySpend();

        $this->assertSame([
            ['campaign_id' => 'campaign-1'],
            ['campaign_id' => 'campaign-2'],
        ], $result['data']);
        Http::assertSentCount(2);
    }
}
