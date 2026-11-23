<?php

namespace App\Models;

use App\Models\MachineModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function attachments()
    {
        return $this->hasMany(CategoryAttachment::class);
    }

    public function machineModels()
    {
        return $this->hasMany(MachineModel::class);
    }
}