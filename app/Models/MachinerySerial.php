<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MachinerySerial extends Model
{
    protected $fillable = [
        'machinery_item_id',
        'serial_number',
        'serial_code',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'serial_number' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(
            MachineryItem::class,
            'machinery_item_id'
        );
    }

    public function machineryItem()
    {
        return $this->belongsTo(MachineryItem::class);
    }
}