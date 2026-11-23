<?php

namespace App\Models;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\MachineModel;
use App\Models\Shipment;
use App\Models\User;
use App\Models\HoldReason;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;


class Customer extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'customer_name',
        'mobile',

        'category_id',
        'machine_model_id',
        'attachments',

        'price',
        'quantity',
        'advance_payment',

        'status',

        'pincode',
        'district',
        'state',
        'address',
        'booking_date',

        'agent_id',
        'user_id',
        'hold_reason_id',
        'hold_remark',
        'bill_number',
        'delivery_date'
    ];

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('screenshots')
            ->singleFile()
            ->useDisk('public');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function machineModel()
    {
        return $this->belongsTo(MachineModel::class);
    }



    protected function casts(): array
    {
        return [
            'attachments' => 'array',
            // 'booking_date' => 'date',
        ];
    }


    protected static function booted(): void
    {
        static::addGlobalScope('activeAgent', function (Builder $builder) {

            $builder->whereHas('agent', function (Builder $query) {
                $query->whereNull('deleted_at');
            });

        });
    }

    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    public function holdReason()
    {
        return $this->belongsTo(HoldReason::class);
    }
}
