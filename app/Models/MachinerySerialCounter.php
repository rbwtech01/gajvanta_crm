<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MachinerySerialCounter extends Model
{
    protected $fillable = [
        'machinery_item_id',
        'next_number',
    ];

    protected function casts(): array
    {
        return [
            'next_number' => 'integer',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(
            MachineryItem::class,
            'machinery_item_id'
        );
    }
}