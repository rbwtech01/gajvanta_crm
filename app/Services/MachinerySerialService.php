<?php

namespace App\Services;

use App\Models\MachineryItem;
use App\Models\MachinerySerial;
use App\Models\MachinerySerialCounter;
use Illuminate\Support\Facades\DB;

class MachinerySerialService
{
    public function generate(MachineryItem $item, int $quantity): int
    {
        return DB::transaction(function () use ($item, $quantity) {

            $counter = MachinerySerialCounter::query()
                ->lockForUpdate()
                ->first();

            if (! $counter) {
                $counter = MachinerySerialCounter::create([
                    'next_number' => 261041,
                ]);
            }

            $startNumber = $counter->next_number;

            $serials = [];

            for ($i = 0; $i < $quantity; $i++) {

                $number = $startNumber + $i;

                $serials[] = [
                    'machinery_item_id' => $item->id,

                    // Numeric sequence
                    'serial_number' => $number,

                    // Actual serial/chassis code
                    'serial_code' => $number . '-' . $item->model_code,

                    'is_active' => true,

                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            MachinerySerial::insert($serials);

            $counter->update([
                'next_number' => $startNumber + $quantity,
            ]);

            return $quantity;
        });
    }
}