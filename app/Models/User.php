<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\AgentInvitationNotification;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'employee_code',
        'name',
        'email',
        'phone',
        'salary',
        'monthly_salary',
        'password',
        'admin_password',
        'is_active',
        'created_by',
        'call_center_id',
        'must_change_password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'salary' => 'decimal:2',
            'monthly_salary' => 'decimal:2',
            'last_login_at' => 'datetime',
            'admin_password' => 'encrypted',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {

            'admin' => $this->hasRole('Admin'),

            'agent' => $this->hasRole('Agent'),

            'operations' => $this->hasRole('Operations Manager'),

            'manager' => $this->hasRole('Call Center Admin'),

            'packing' => $this->hasRole('Packing'),

            'shipment' => $this->hasRole('Shipment'),

            'supervisor' => $this->hasRole('Supervisor'),

            default => false,
        };
    }

    protected static function booted(): void
    {
        static::created(function (User $user) {
            $user->update([
                'employee_code' => 'EMP-'.str_pad($user->id, 3, '0', STR_PAD_LEFT),
            ]);
        });
    }

    public function callCenter()
    {
        return $this->belongsTo(CallCenter::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new AgentInvitationNotification(token: $token));
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'agent_id');
    }

    public function agentTargets(): HasMany
    {
        return $this->hasMany(AgentTarget::class, 'agent_id');
    }
}
