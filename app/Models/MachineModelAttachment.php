<?php

namespace App\Models;

use App\Models\MachineModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MachineModelAttachment extends Model
{
     protected $fillable = [
        'machine_model_id',
        'title',
        'slug',
        'status',
    ];

    protected static function booted(): void
    {
        static::creating(function ($attachment) {
            if (blank($attachment->slug)) {
                $attachment->slug = Str::slug($attachment->title);
            }
        });
    }

    public function machineModel()
    {
        return $this->belongsTo(MachineModel::class);
    }

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(
            Customer::class,
            'customer_attachment',
            'machine_model_attachment_id',
            'customer_id'
        );
    }
}
