<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetaAdReport extends Model
{
    protected $fillable = [
        'agent_id',
        'report_date',
        'campaign_name',
        'ad_id',
        'ad_name',
        'budget',
        'budget_type',
        'amount_spent',
        'campaign_id',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'budget' => 'decimal:2',
            'amount_spent' => 'decimal:2',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}