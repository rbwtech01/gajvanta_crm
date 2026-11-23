<?php

namespace Tests\Feature;

use App\Filament\Pages\EmployeeDailyExpenseReport;
use App\Filament\Pages\MetaAdsReport;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MetaAdsReportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('employee_code')->nullable()->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->decimal('salary', 12, 2)->default(10000);
            $table->decimal('monthly_salary', 12, 2)->default(10000);
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
            $table->foreignId('agent_id');
            $table->string('campaign_id')->nullable();
            $table->date('report_date');
            $table->string('campaign_name');
            $table->string('ad_id')->nullable();
            $table->string('ad_name')->nullable();
            $table->decimal('budget', 12, 2)->default(0);
            $table->string('budget_type')->nullable();
            $table->decimal('amount_spent', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('agent_id')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->date('booking_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function test_it_compares_daily_agent_revenue_to_budget_spend_and_salary_target(): void
    {
        $agent = User::query()->create([
            'name' => 'Asha Verma',
            'email' => 'asha@example.test',
            'phone' => '9876543210',
            'salary' => 10000,
            'monthly_salary' => 20000,
            'password' => 'password',
        ]);
        $agentRole = Role::create([
            'name' => 'Agent',
            'guard_name' => 'web',
        ]);
        $agent->assignRole($agentRole);
        $otherAgent = User::query()->create([
            'name' => 'Misti Sharma',
            'email' => 'misti@example.test',
            'phone' => '9876543212',
            'salary' => 10000,
            'monthly_salary' => 20000,
            'password' => 'password',
        ]);
        $otherAgent->assignRole($agentRole);
        $creator = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.test',
            'phone' => '9876543211',
            'salary' => 10000,
            'monthly_salary' => 20000,
            'password' => 'password',
        ]);

        foreach ([
            ['campaign-1', 'Asha Campaign 1', 'ad-1', 500, 100],
            ['campaign-1', 'Asha Campaign 1', 'ad-2', 500, 200],
            ['campaign-2', 'Asha Campaign 2', 'ad-3', 200, 300],
        ] as [$campaignId, $campaignName, $adId, $budget, $spend]) {
            DB::table('meta_ad_reports')->insert([
                'agent_id' => $agent->id,
                'campaign_id' => $campaignId,
                'report_date' => '2026-10-30',
                'campaign_name' => $campaignName,
                'ad_id' => $adId,
                'budget' => $budget,
                'amount_spent' => $spend,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('meta_ad_reports')->insert([
            'agent_id' => $otherAgent->id,
            'campaign_id' => 'campaign-3',
            'report_date' => '2026-10-30',
            'campaign_name' => 'Misti Campaign',
            'ad_id' => 'ad-4',
            'budget' => 300,
            'amount_spent' => 400,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('customers')->insert([
            'user_id' => $creator->id,
            'agent_id' => $agent->id,
            'price' => 1500,
            'booking_date' => '2026-10-30',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Livewire::actingAs($agent)
            ->test(MetaAdsReport::class)
            ->assertSeeInOrder([
                '#',
                'Date',
                'Agent Name',
                'Campaigns',
                'Agent Revenue',
                'Total Budget',
                'Total Amount Spent',
                'Salary / Day',
                'Spend Share',
                'ROAS',
                'Marketing %',
                'Spend - Daily Salary',
            ])
            ->assertSee('₹700.00')
            ->assertSee('₹600.00')
            ->assertSee('₹400.00')
            ->assertSee('₹1,500.00')
            ->assertSee('₹322.58')
            ->assertSee('Total Salary / Day')
            ->assertSee('₹645.16')
            ->assertSee('60.00%')
            ->assertSee('40.00%')
            ->assertSee('Total Spend Share')
            ->assertSee('100.00%')
            ->assertSee('ROAS')
            ->assertSee('2.50x')
            ->assertSee('Marketing %')
            ->assertSee('40.00%')
            ->assertSee('—')
            ->assertSee('₹277.42')
            ->assertSee('₹600.00 ad spend - ₹322.58 salary/day')
            ->assertSee('Overall Spend - Daily Salary')
            ->assertSee('₹354.84')
            ->assertSee('₹1,000.00')
            ->assertDontSee('Daily Revenue Needed');

        Livewire::actingAs($agent)
            ->test(EmployeeDailyExpenseReport::class)
            ->set('selectedMonth', '2026-10')
            ->assertSee('₹322.58');
    }

    public function test_it_filters_meta_ads_reports_with_a_quick_date_preset(): void
    {
        Carbon::setTestNow('2026-11-17 12:00:00');

        $agent = User::query()->create([
            'name' => 'Asha Verma',
            'email' => 'asha@example.test',
            'phone' => '9876543210',
            'salary' => 10000,
            'monthly_salary' => 20000,
            'password' => 'password',
        ]);
        $agentRole = Role::create([
            'name' => 'Agent',
            'guard_name' => 'web',
        ]);
        $agent->assignRole($agentRole);

        foreach ([
            ['2026-11-17', 100],
            ['2026-11-16', 200],
            ['2026-11-15', 300],
        ] as [$reportDate, $amountSpent]) {
            DB::table('meta_ad_reports')->insert([
                'agent_id' => $agent->id,
                'campaign_id' => 'campaign-'.$reportDate,
                'report_date' => $reportDate,
                'campaign_name' => 'Campaign '.$reportDate,
                'budget' => $amountSpent,
                'amount_spent' => $amountSpent,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Livewire::actingAs($agent)
            ->test(MetaAdsReport::class)
            ->assertSeeInOrder(['17 Nov 2026', '16 Nov 2026', '15 Nov 2026'])
            ->set('tableDeferredFilters.report_date.period', 'yesterday')
            ->call('applyTableFilters')
            ->assertSee('₹200.00')
            ->assertDontSee('₹100.00')
            ->assertDontSee('₹300.00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }
}
