<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MachineryItem extends Model
{
    protected $fillable = [
        'machinery_category_id',
        'name',
        'model_code',
        'model_tag',
        'dealer_price',
        'mrp',
        'mop',
        'tax_rate',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'dealer_price' => 'decimal:2',
            'mrp' => 'decimal:2',
            'mop' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(
            MachineryCategory::class,
            'machinery_category_id'
        );
    }

    public function serials(): HasMany
    {
        return $this->hasMany(
            MachinerySerial::class,
            'machinery_item_id'
        );
    }

    public function serialCounter(): HasOne
    {
        return $this->hasOne(
            MachinerySerialCounter::class,
            'machinery_item_id'
        );
    }
}