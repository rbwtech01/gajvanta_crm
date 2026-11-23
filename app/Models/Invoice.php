<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Support\Str;

class Invoice extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'invoice_no',

        'customer_id',
        'agent_id',

        'customer_name',
        'mobile',
        'address',
        'district',
        'state',
        'pincode',

        'category',
        'machine_model',
        'attachments',

        'price',
        'advance_payment',
        'balance',

        'remarks',

        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'attachments' => 'array',
            'generated_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('invoice_pdf')
            ->singleFile();

        $this->addMediaCollection('invoice_documents');
    }


    protected static function booted(): void
    {
        static::creating(function ($invoice) {

            if (! $invoice->invoice_no) {

                $lastInvoice = self::latest('id')->first();

                $nextNumber = $lastInvoice
                    ? ((int) substr($lastInvoice->invoice_no, -4)) + 1
                    : 1001;

                $invoice->invoice_no = 'INV-' . now()->year . '-' . $nextNumber;
            }

        });
    }
}