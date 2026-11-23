<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SyncMetaAdsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('employee_code')->nullable()->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('model_has_roles', function (Blueprint $table): void {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->primary(['role_id', 'model_id', 'model_type']);
        });

        Schema::create('meta_ad_reports', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('agent_id');
            $table->string('campaign_id', 50)->nullable();
            $table->string('ad_id', 50)->nullable();
            $table->date('report_date');
            $table->string('campaign_name');
            $table->string('ad_name')->nullable();
            $table->decimal('budget', 12, 2)->default(0);
            $table->string('budget_type', 20)->nullable();
            $table->decimal('amount_spent', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function test_it_saves_each_ad_for_a_campaign_across_api_pages(): void
    {
        config([
            'meta.api_version' => 'v24.0',
            'meta.ad_account_id' => '123456789',
            'meta.ad_account_ids' => ['123456789'],
            'meta.access_token' => 'test-token',
        ]);

        $agent = User::query()->create([
            'name' => 'Asha Verma',
            'email' => 'asha@example.test',
            'phone' => '9876543210',
            'password' => 'password',
            'is_active' => true,
        ]);
        $agent->assignRole(Role::create([
            'name' => 'Agent',
            'guard_name' => 'web',
        ]));

        $reportDate = now('Asia/Kolkata')->subDay()->toDateString();
        $ad = fn (string $id, string $spend): array => [
            'campaign_id' => 'campaign-1',
            'campaign_name' => 'Asha 26OCT R-505',
            'ad_id' => $id,
            'ad_name' => "Ad {$id}",
            'spend' => $spend,
            'date_start' => $reportDate,
            'date_stop' => $reportDate,
        ];

        DB::table('meta_ad_reports')->insert([
            'agent_id' => $agent->id,
            'campaign_id' => 'campaign-1',
            'report_date' => $reportDate,
            'campaign_name' => 'Asha 26OCT R-505',
            'budget' => 45,
            'amount_spent' => 60.75,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $responses = [
            [
                'data' => [
                    $ad('12345001', '12.50'),
                    $ad('12345002', '20.25'),
                ],
                'paging' => [
                    'next' => 'https://graph.facebook.com/next',
                    'cursors' => ['after' => 'next-cursor'],
                ],
            ],
            ['data' => [$ad('12345003', '28.00')]],
        ];
        $insightRequestCount = 0;

        Http::fake(function ($request) use (&$insightRequestCount, $responses) {
            $path = (string) parse_url($request->url(), PHP_URL_PATH);

            if (str_ends_with($path, '/insights')) {
                return Http::response($responses[$insightRequestCount++]);
            }

            if (str_ends_with($path, '/adsets')) {
                return Http::response([
                    'data' => [
                        ['lifetime_budget' => '4500'],
                    ],
                ]);
            }

            return Http::response([
                'daily_budget' => '0',
                'lifetime_budget' => '0',
            ]);
        });

        $this->artisan('app:sync-meta-ads')
            ->expectsOutputToContain('Total API records fetched: 3')
            ->expectsOutputToContain('Processed: 3')
            ->assertExitCode(0);

        $this->assertSame(2, $insightRequestCount);
        Http::assertSent(fn ($request) => str_ends_with(
            (string) parse_url($request->url(), PHP_URL_PATH),
            '/campaign-1/adsets'
        ));
        $this->assertDatabaseCount('meta_ad_reports', 3);
        $this->assertEquals(
            12.50,
            (float) DB::table('meta_ad_reports')
                ->where('ad_id', '12345001')
                ->value('amount_spent')
        );
        $this->assertEquals(
            60.75,
            (float) DB::table('meta_ad_reports')
                ->where('campaign_id', 'campaign-1')
                ->sum('amount_spent')
        );
        $this->assertEquals(
            45.0,
            (float) DB::table('meta_ad_reports')->sum('budget')
        );
        $this->assertDatabaseHas('meta_ad_reports', [
            'ad_id' => '12345001',
            'budget' => 45.0,
            'budget_type' => 'lifetime',
        ]);
        $this->assertDatabaseHas('meta_ad_reports', [
            'ad_id' => '12345001',
            'ad_name' => 'Ad 12345001',
            'amount_spent' => 12.50,
        ]);
        $this->assertDatabaseHas('meta_ad_reports', [
            'ad_id' => '12345002',
            'ad_name' => 'Ad 12345002',
            'amount_spent' => 20.25,
        ]);
        $this->assertDatabaseHas('meta_ad_reports', [
            'ad_id' => '12345003',
            'ad_name' => 'Ad 12345003',
            'amount_spent' => 28.00,
        ]);
    }

    public function test_it_syncs_ads_from_multiple_accounts(): void
    {
        config([
            'meta.api_version' => 'v24.0',
            'meta.ad_account_ids' => ['111111111', '222222222'],
            'meta.access_token' => 'test-token',
        ]);

        $agent = User::query()->create([
            'name' => 'Asha Verma',
            'email' => 'asha@example.test',
            'phone' => '9876543210',
            'password' => 'password',
            'is_active' => true,
        ]);
        $agent->assignRole(Role::create([
            'name' => 'Agent',
            'guard_name' => 'web',
        ]));

        $reportDate = now('Asia/Kolkata')->subDay()->toDateString();
        $accountAds = [
            '111111111' => [
                'campaign_id' => 'campaign-1',
                'ad_id' => 'ad-1',
            ],
            '222222222' => [
                'campaign_id' => 'campaign-2',
                'ad_id' => 'ad-2',
            ],
        ];

        Http::fake(function ($request) use ($accountAds, $reportDate) {
            $path = (string) parse_url($request->url(), PHP_URL_PATH);

            foreach ($accountAds as $accountId => $ids) {
                if (str_ends_with($path, "/act_{$accountId}/insights")) {
                    return Http::response([
                        'data' => [[
                            ...$ids,
                            'campaign_name' => 'Asha 26OCT R-505',
                            'ad_name' => "Ad {$ids['ad_id']}",
                            'spend' => '15.00',
                            'date_start' => $reportDate,
                            'date_stop' => $reportDate,
                        ]],
                    ]);
                }
            }

            return Http::response([
                'daily_budget' => '1000',
                'lifetime_budget' => '0',
            ]);
        });

        $this->artisan('app:sync-meta-ads')
            ->expectsOutputToContain('Processed: 2')
            ->assertExitCode(0);

        foreach (array_keys($accountAds) as $accountId) {
            Http::assertSent(fn ($request) => str_ends_with(
                (string) parse_url($request->url(), PHP_URL_PATH),
                "/act_{$accountId}/insights"
            ));
        }

        $this->assertDatabaseCount('meta_ad_reports', 2);
        $this->assertDatabaseHas('meta_ad_reports', [
            'ad_id' => 'ad-1',
            'amount_spent' => 15.00,
        ]);
        $this->assertDatabaseHas('meta_ad_reports', [
            'ad_id' => 'ad-2',
            'amount_spent' => 15.00,
        ]);
    }
}
