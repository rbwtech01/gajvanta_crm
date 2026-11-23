<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AgentSalaryActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('call_center_id')->nullable();
            $table->string('employee_code')->nullable()->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('password');
            $table->decimal('salary', 12, 2)->default(10000);
            $table->decimal('monthly_salary', 12, 2)->default(15000);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('call_centers', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->date('booking_date')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('agent_targets', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('agent_id');
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->decimal('target_amount', 15, 2);
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('role_has_permissions', function (Blueprint $table): void {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
            $table->primary(['permission_id', 'role_id']);
        });

        Schema::create('model_has_permissions', function (Blueprint $table): void {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->primary(['permission_id', 'model_id', 'model_type']);
        });

        Schema::create('model_has_roles', function (Blueprint $table): void {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->primary(['role_id', 'model_id', 'model_type']);
        });
    }

    public function test_admin_can_update_an_agents_salary_from_the_agent_list(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.test',
            'phone' => '9876543210',
            'password' => 'password',
        ]);
        $admin->assignRole(Role::create([
            'name' => 'Admin',
            'guard_name' => 'web',
        ]));

        $agent = User::query()->create([
            'name' => 'Asha Verma',
            'email' => 'asha@example.test',
            'phone' => '9876543211',
            'password' => 'password',
        ]);
        $agent->assignRole(Role::create([
            'name' => 'Agent',
            'guard_name' => 'web',
        ]));

        $this->assertSame('10000.00', $agent->fresh()->salary);

        Livewire::actingAs($admin)
            ->test(ListUsers::class)
            ->callTableAction('setSalary', $agent->getKey(), [
                'salary' => 18000,
            ]);

        $this->assertSame('18000.00', $agent->fresh()->salary);
    }
}
