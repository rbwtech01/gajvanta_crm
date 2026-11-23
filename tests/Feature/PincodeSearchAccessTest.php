<?php

namespace Tests\Feature;

use App\Filament\Agent\Pages\PincodeSearch;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class PincodeSearchAccessTest extends TestCase
{
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

        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('agent_id')->nullable();
            $table->string('pincode');
            $table->string('district')->nullable();
            $table->string('state')->nullable();
            $table->string('status')->nullable();
            $table->date('booking_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function test_agent_can_search_matching_bookings_created_by_other_agents(): void
    {
        $signedInAgent = $this->createAgent('signed-in@example.test', 'Signed-in Agent');
        $otherAgent = $this->createAgent('other@example.test', 'Other Agent');

        $this->createBooking($signedInAgent, $signedInAgent, 'Created');
        $this->createBooking($otherAgent, $otherAgent, 'Delivered');

        Livewire::actingAs($signedInAgent)
            ->test(PincodeSearch::class)
            ->set('pincode', '852125')
            ->call('search')
            ->assertSee('2 records')
            ->assertSee('Signed-in Agent')
            ->assertSee('Other Agent')
            ->assertSee('Created')
            ->assertSee('Delivered');
    }

    private function createAgent(string $email, string $name): User
    {
        return User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => 'password',
        ]);
    }

    private function createBooking(User $owner, User $responsibleAgent, string $status): void
    {
        DB::table('customers')->insert([
            'user_id' => $owner->id,
            'agent_id' => $responsibleAgent->id,
            'pincode' => '852125',
            'district' => 'Sample District',
            'state' => 'Bihar',
            'status' => $status,
            'booking_date' => '2026-10-10',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
