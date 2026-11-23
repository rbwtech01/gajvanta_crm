<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitingOrder extends Model
{
    protected $fillable = [
        'agent_id',
        'visitor_name',
        'primary_mobile',
        'secondary_mobile',
        'pincode',
        'city',
        'state',
        'remark',
        'visit_date',
        'cooling_until',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'cooling_until' => 'datetime',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}