<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HoldReason;

class HoldReasonSeeder extends Seeder
{
    public function run(): void
    {
        $reasons = [
            'Pincode Issue',
            'Open Delivery',
            'Price Issue',
            'Attachment Issue',
            'Long Term Stock Issue',
            'Ordered & Delivery Scheduled'
        ];

        foreach ($reasons as $reason) {
            HoldReason::updateOrCreate(
                ['reason' => $reason],
                ['reason' => $reason]
            );
        }
    }
}