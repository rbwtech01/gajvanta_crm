<?php

namespace Tests\Feature;

use App\Filament\Pages\AdminPincodeSearch;
use App\Models\Category;
use App\Models\MachineModel;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPincodeSearchTest extends TestCase
{
    private User $admin;

    private User $bookingAgent;

    private Category $category;

    private MachineModel $machineModel;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('employee_code')->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('machine_models', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id');
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->string('customer_name');
            $table->string('mobile')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->unsignedInteger('quantity')->nullable();
            $table->decimal('advance_payment', 10, 2)->nullable();
            $table->string('status')->nullable();
            $table->string('pincode');
            $table->string('state')->nullable();
            $table->string('district')->nullable();
            $table->text('address')->nullable();
            $table->foreignId('agent_id')->nullable();
            $table->foreignId('category_id')->nullable();
            $table->foreignId('machine_model_id')->nullable();
            $table->date('booking_date')->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('bill_number')->nullable();
            $table->string('hold_remark')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $this->admin = $this->createUser('admin@example.test', 'Admin User');
        $this->bookingAgent = $this->createUser('agent@example.test', 'Booking Agent');

        $this->category = Category::query()->create([
            'name' => 'Harvesters',
            'slug' => 'harvesters',
        ]);

        $this->machineModel = MachineModel::query()->create([
            'category_id' => $this->category->id,
            'name' => 'HarvestPro 500',
            'slug' => 'harvestpro-500',
        ]);
    }

    public function test_admin_sees_full_machine_and_customer_details_across_agents(): void
    {
        DB::table('customers')->insert([
            'user_id' => $this->bookingAgent->id,
            'customer_name' => 'Vivek Kumar',
            'mobile' => '9876543210',
            'price' => 3000,
            'quantity' => 2,
            'advance_payment' => 500,
            'status' => 'delivered',
            'pincode' => '852125',
            'state' => 'Bihar',
            'district' => 'Sample District',
            'address' => '12 Main Road',
            'agent_id' => $this->bookingAgent->id,
            'category_id' => $this->category->id,
            'machine_model_id' => $this->machineModel->id,
            'booking_date' => '2026-10-01',
            'delivery_date' => '2026-10-05',
            'bill_number' => 'BILL-102',
            'hold_remark' => 'Customer confirmed delivery',
            'created_at' => '2026-10-01 12:00:00',
            'updated_at' => '2026-10-05 12:00:00',
        ]);

        Livewire::actingAs($this->admin)
            ->test(AdminPincodeSearch::class)
            ->set('pincode', '852125')
            ->call('search')
            ->assertSee('Vivek Kumar')
            ->assertSee('9876543210')
            ->assertSee('Booking Agent')
            ->assertSee('HarvestPro 500')
            ->assertSee('Harvesters')
            ->assertSee('12 Main Road')
            ->assertSee('BILL-102')
            ->assertSee('Customer confirmed delivery')
            ->assertSee('2');
    }

    public function test_admin_search_validates_pincode_and_shows_empty_state(): void
    {
        Livewire::actingAs($this->admin)
            ->test(AdminPincodeSearch::class)
            ->set('pincode', '85212')
            ->call('search')
            ->assertHasErrors(['pincode' => 'regex'])
            ->set('pincode', '123456')
            ->call('search')
            ->assertSee('No booking history found');
    }

    private function createUser(string $email, string $name): User
    {
        return User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => 'password',
        ]);
    }
}
