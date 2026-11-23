<?php

namespace Tests\Feature;

use App\Filament\Resources\UserPasswords\Pages\ListUserPasswords;
use App\Filament\Resources\UserPasswords\UserPasswordResource;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AgentPasswordResetTest extends TestCase
{
    private User $admin;

    private User $activeAgent;

    private User $inactiveAgent;

    private User $amazonAgent;

    private User $amazonStoreAgent;

    private Role $agentRole;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('call_centers', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('call_center_id')->nullable();
            $table->string('employee_code')->nullable()->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('password');
            $table->text('admin_password')->nullable();
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

        $adminRole = Role::create(['name' => 'Admin', 'guard_name' => 'web']);
        $this->agentRole = Role::create(['name' => 'Agent', 'guard_name' => 'web']);

        $this->admin = $this->createUser('Admin', 'admin@example.test', true);
        $this->activeAgent = $this->createUser('Active Agent', 'active@example.test', true);
        $this->inactiveAgent = $this->createUser('Inactive Agent', 'inactive@example.test', false);
        $this->amazonAgent = $this->createUser('Amazon', 'amazon@example.test', true);

        $amazonStoreCallCenterId = DB::table('call_centers')->insertGetId([
            'name' => 'Amazon Store',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->amazonStoreAgent = $this->createUser(
            'Amazon Store Agent',
            'amazon-store@example.test',
            true,
            $amazonStoreCallCenterId,
        );

        $this->admin->assignRole($adminRole);
        $this->activeAgent->assignRole($this->agentRole);
        $this->inactiveAgent->assignRole($this->agentRole);
        $this->amazonAgent->assignRole($this->agentRole);
        $this->amazonStoreAgent->assignRole($this->agentRole);
    }

    public function test_admin_can_reset_an_active_agents_password(): void
    {
        $recordIds = UserPasswordResource::getEloquentQuery()->pluck('id')->all();

        $this->assertContains($this->admin->id, $recordIds);
        $this->assertContains($this->activeAgent->id, $recordIds);
        $this->assertNotContains($this->inactiveAgent->id, $recordIds);
        $this->assertNotContains($this->amazonAgent->id, $recordIds);
        $this->assertNotContains($this->amazonStoreAgent->id, $recordIds);

        Livewire::actingAs($this->admin)
            ->test(ListUserPasswords::class)
            ->filterTable('role', $this->agentRole)
            ->assertCanSeeTableRecords([$this->activeAgent])
            ->assertCanNotSeeTableRecords([
                $this->admin,
                $this->inactiveAgent,
                $this->amazonAgent,
                $this->amazonStoreAgent,
            ])
            ->callTableAction('resetPassword', $this->activeAgent->id, [
                'password' => 'NewAgentPassword123',
                'password_confirmation' => 'NewAgentPassword123',
            ]);

        $this->assertTrue(Hash::check('NewAgentPassword123', $this->activeAgent->fresh()->password));
        $this->assertSame('NewAgentPassword123', $this->activeAgent->fresh()->admin_password);
    }

    private function createUser(string $name, string $email, bool $isActive, ?int $callCenterId = null): User
    {
        return User::query()->create([
            'call_center_id' => $callCenterId,
            'name' => $name,
            'email' => $email,
            'phone' => '9876543210',
            'password' => 'OldPassword123',
            'admin_password' => 'OldPassword123',
            'is_active' => $isActive,
        ]);
    }
}
