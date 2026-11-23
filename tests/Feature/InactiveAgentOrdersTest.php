<?php

namespace Tests\Feature;

use App\Filament\Pages\OrderCalendar;
use App\Filament\Pages\OrdersByDate;
use App\Filament\Agent\Pages\Auth\Login as AgentLogin;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InactiveAgentOrdersTest extends TestCase
{
    private User $admin;

    private User $inactiveAgent;

    private User $regularAgent;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('employee_code')->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
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

        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('agent_id');
            $table->date('booking_date');
            $table->decimal('price', 10, 2);
            $table->string('status')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('shipments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->string('tracking_id')->nullable();
            $table->timestamps();
        });

        $adminRole = Role::create(['name' => 'Admin', 'guard_name' => 'web']);
        $agentRole = Role::create(['name' => 'Agent', 'guard_name' => 'web']);

        $this->admin = $this->createUser('Admin', 'admin@example.test', true);
        $this->admin->assignRole($adminRole);

        $this->inactiveAgent = $this->createUser('Inactive Agent', 'inactive@example.test', false);
        $this->inactiveAgent->assignRole($agentRole);

        $this->regularAgent = $this->createUser('Regular Agent', 'regular@example.test', true);
        $this->regularAgent->assignRole($agentRole);

        DB::table('customers')->insert([
            'agent_id' => $this->inactiveAgent->id,
            'booking_date' => '2026-03-10',
            'price' => 1250,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_admin_can_view_inactive_agent_in_calendar_and_orders(): void
    {
        Livewire::actingAs($this->admin)
            ->test(OrderCalendar::class)
            ->assertSee('Inactive Agent');

        $this->actingAs($this->admin)
            ->getJson('/calendar-events?start=2026-03-01&end=2026-03-31&agent='.$this->inactiveAgent->id)
            ->assertOk()
            ->assertJsonPath('0.count', 1)
            ->assertJsonPath('0.amount', 1250);

        $ordersPage = Livewire::actingAs($this->admin)
            ->test(OrdersByDate::class)
            ->set('agent', $this->inactiveAgent->id)
            ->set('date', '2026-03-10');

        $this->assertCount(1, $ordersPage->instance()->getOrders());
    }

    public function test_non_admin_cannot_view_inactive_agent_orders(): void
    {
        $this->actingAs($this->regularAgent)
            ->getJson('/calendar-events?start=2026-03-01&end=2026-03-31&agent='.$this->inactiveAgent->id)
            ->assertNotFound();

        $ordersPage = Livewire::actingAs($this->regularAgent)
            ->test(OrdersByDate::class)
            ->set('agent', $this->inactiveAgent->id)
            ->set('date', '2026-03-10');

        $this->assertCount(0, $ordersPage->instance()->getOrders());
    }

    public function test_inactive_agent_panel_authorization_remains_role_based(): void
    {
        $agentPanel = Panel::make()->id('agent');

        $this->assertTrue($this->inactiveAgent->canAccessPanel($agentPanel));
        $this->assertTrue($this->regularAgent->canAccessPanel($agentPanel));
    }

    public function test_inactive_agent_with_correct_password_sees_access_message(): void
    {
        Filament::setCurrentPanel('agent');

        Livewire::test(AgentLogin::class)
            ->set('data.email', 'inactive@example.test')
            ->set('data.password', 'password')
            ->call('authenticate')
            ->assertHasErrors([
                'data.email' => 'You do not have access to the agent portal. Please contact your administrator.',
            ]);
    }

    private function createUser(string $name, string $email, bool $isActive): User
    {
        return User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => 'password',
            'is_active' => $isActive,
        ]);
    }
}
