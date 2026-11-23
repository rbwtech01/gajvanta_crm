<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CallCenter extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'slug',
        'address',
        'pincode',
        'city',
        'state',
        'is_active',
    ];

    protected static function booted(): void
    {
        static::creating(function (CallCenter $callCenter) {

            $lastId = static::withTrashed()->max('id') + 1;

            $callCenter->code = 'CC-' . str_pad($lastId, 3, '0', STR_PAD_LEFT);
        });
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}