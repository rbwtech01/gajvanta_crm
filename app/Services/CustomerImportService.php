<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Customer;
use App\Models\MachineModel;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use RuntimeException;

class CustomerImportService
{
    /**
     * Import the first row from the uploaded Excel file.
     *
     * First test:
     * - One row only
     * - Category lookup
     * - Agent ownership
     * - Basic customer fields
     * - Booking date/time
     * - Machine model and attachments remain optional
     */
    public function import(string $filePath, int $agentId): Collection
    {
        $rows = Excel::toCollection(
            new class implements ToCollection, WithHeadingRow {
                public function collection(Collection $rows): void
                {
                    // Laravel Excel passes all data rows here.
                }
            },
            $filePath
        )->first();

        if (! $rows || $rows->isEmpty()) {
            throw new RuntimeException('The Excel file is empty.');
        }

        return DB::transaction(function () use ($rows, $agentId) {

            $customers = collect();

            foreach ($rows as $row) {

                /*
                |--------------------------------------------------------------------------
                | SKIP COMPLETELY EMPTY ROWS
                |--------------------------------------------------------------------------
                */

                if (
                    blank($row['customer_name'] ?? null) &&
                    blank($row['mobile'] ?? null) &&
                    blank($row['category'] ?? null)
                ) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | CUSTOMER NAME
                |--------------------------------------------------------------------------
                */

                $customerName = trim(
                    (string) ($row['customer_name'] ?? '')
                );

                if ($customerName === '') {
                    throw new RuntimeException(
                        'Customer Name is required.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | CATEGORY
                |--------------------------------------------------------------------------
                */

                $categoryName = trim(
                    (string) ($row['category'] ?? '')
                );

                if ($categoryName === '') {
                    throw new RuntimeException(
                        "Category is required for customer '{$customerName}'."
                    );
                }

                $category = Category::query()
                    ->whereRaw(
                        'LOWER(TRIM(name)) = ?',
                        [strtolower($categoryName)]
                    )
                    ->first();

                if (! $category) {
                    throw new RuntimeException(
                        "Category '{$categoryName}' was not found for customer '{$customerName}'."
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | MACHINE MODEL
                |--------------------------------------------------------------------------
                */

                $machineModelName = trim(
                    (string) ($row['machine_model'] ?? '')
                );

                $machineModel = null;

                if ($machineModelName !== '') {

                    $machineModel = MachineModel::query()
                    ->where('category_id', $category->id)
                    ->whereRaw(
                        'LOWER(TRIM(name)) = ?',
                        [strtolower($machineModelName)]
                    )
                    ->first();

                    if (! $machineModel) {
                        throw new RuntimeException(
                            "Machine Model '{$machineModelName}' was not found under category '{$categoryName}' for customer '{$customerName}'."
                        );
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | CREATE CUSTOMER
                |--------------------------------------------------------------------------
                */

                $customer = new Customer();

                /*
                |--------------------------------------------------------------------------
                | AGENT OWNERSHIP
                |--------------------------------------------------------------------------
                */

                $customer->user_id = $agentId;
                $customer->agent_id = $agentId;

                /*
                |--------------------------------------------------------------------------
                | BASIC FIELDS
                |--------------------------------------------------------------------------
                */

                $customer->customer_name = $customerName;

                $customer->mobile = blank($row['mobile'] ?? null)
                    ? null
                    : trim((string) $row['mobile']);

                /*
                |--------------------------------------------------------------------------
                | CATEGORY
                |--------------------------------------------------------------------------
                */

                $customer->category_id = $category->id;

                /*
                |--------------------------------------------------------------------------
                | MACHINE MODEL
                |--------------------------------------------------------------------------
                */

                $customer->machine_model_id = $machineModel?->id;

                /*
                |--------------------------------------------------------------------------
                | ATTACHMENTS
                |--------------------------------------------------------------------------
                |
                | Leave empty for now.
                |
                */

                $customer->attachments = null;

                /*
                |--------------------------------------------------------------------------
                | PRICE
                |--------------------------------------------------------------------------
                */

                $price = $row['price'] ?? null;

                $customer->price = blank($price)
                    ? 0
                    : (float) str_replace(
                        ',',
                        '',
                        (string) $price
                    );

                /*
                |--------------------------------------------------------------------------
                | ADVANCE PAYMENT
                |--------------------------------------------------------------------------
                */

                $advancePayment = $row['advance_payment'] ?? null;

                $customer->advance_payment = blank($advancePayment)
                    ? 0
                    : (float) str_replace(
                        ',',
                        '',
                        (string) $advancePayment
                    );

                /*
                |--------------------------------------------------------------------------
                | BOOKING DATE
                |--------------------------------------------------------------------------
                */

                $customer->booking_date = $this->parseExcelDate(
                    $row['booking_date'] ?? null
                );

                /*
                |--------------------------------------------------------------------------
                | BOOKING TIME
                |--------------------------------------------------------------------------
                */

                $customer->booking_time = $this->parseExcelTime(
                    $row['booking_time'] ?? null
                );

                /*
                |--------------------------------------------------------------------------
                | LOCATION
                |--------------------------------------------------------------------------
                */

                $customer->district = trim(
                    (string) ($row['district'] ?? '')
                );

                $customer->pincode = trim(
                    (string) ($row['pincode'] ?? '')
                );

                $customer->state = trim(
                    (string) ($row['state'] ?? '')
                );

                $customer->address = trim(
                    (string) ($row['address'] ?? '')
                );

                /*
                |--------------------------------------------------------------------------
                | STATUS
                |--------------------------------------------------------------------------
                */

                $status = trim(
                    (string) ($row['status'] ?? '')
                );

                $customer->status = $status !== ''
                    ? $status
                    : 'created';

                /*
                |--------------------------------------------------------------------------
                | SAVE
                |--------------------------------------------------------------------------
                */

                $customer->save();

                /*
                |--------------------------------------------------------------------------
                | ADD TO RESULT
                |--------------------------------------------------------------------------
                */

                $customers->push($customer);
            }

            /*
            |--------------------------------------------------------------------------
            | NO VALID ROWS
            |--------------------------------------------------------------------------
            */

            if ($customers->isEmpty()) {
                throw new RuntimeException(
                    'No valid customer rows were found in the Excel file.'
                );
            }

            return $customers;
        });
    }

    /**
     * Convert Excel date into database Y-m-d format.
     */
    private function parseExcelDate(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        /*
         * Excel native date value.
         *
         * Example:
         * 45881
         */
        if (is_numeric($value)) {
            try {
                return Carbon::instance(
                    ExcelDate::excelToDateTimeObject((float) $value)
                )->format('Y-m-d');
            } catch (\Throwable $e) {
                throw new RuntimeException(
                    "Invalid Excel booking date: {$value}"
                );
            }
        }

        $value = trim((string) $value);

        /*
         * DD/MM/YYYY
         *
         * Example:
         * 12/08/2025
         */
        try {
            return Carbon::createFromFormat(
                'd/m/Y',
                $value
            )->format('Y-m-d');
        } catch (\Throwable $e) {
            // Continue trying other common formats.
        }

        /*
         * DD-MM-YYYY
         *
         * Example:
         * 12-08-2025
         */
        try {
            return Carbon::createFromFormat(
                'd-m-Y',
                $value
            )->format('Y-m-d');
        } catch (\Throwable $e) {
            // Continue.
        }

        /*
         * YYYY-MM-DD
         */
        try {
            return Carbon::createFromFormat(
                'Y-m-d',
                $value
            )->format('Y-m-d');
        } catch (\Throwable $e) {
            throw new RuntimeException(
                "Invalid booking date '{$value}'."
            );
        }
    }

    /**
     * Convert Excel time into database H:i:s format.
     */
    private function parseExcelTime(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        /*
         * Excel can store time as a decimal fraction.
         *
         * Example:
         * 10:30 AM can be stored internally as 0.4375
         */
        if (is_numeric($value)) {
            try {
                $seconds = (int) round(
                    ((float) $value) * 24 * 60 * 60
                );

                $hours = intdiv($seconds, 3600);

                $minutes = intdiv(
                    $seconds % 3600,
                    60
                );

                $seconds = $seconds % 60;

                return sprintf(
                    '%02d:%02d:%02d',
                    $hours,
                    $minutes,
                    $seconds
                );
            } catch (\Throwable $e) {
                throw new RuntimeException(
                    "Invalid booking time: {$value}"
                );
            }
        }

        $value = trim((string) $value);

        /*
         * 10:30
         */
        foreach (['H:i', 'H:i:s', 'h:i A', 'h:i:s A'] as $format) {
            try {
                return Carbon::createFromFormat(
                    $format,
                    $value
                )->format('H:i:s');
            } catch (\Throwable $e) {
                // Try next format.
            }
        }

        throw new RuntimeException(
            "Invalid booking time '{$value}'."
        );
    }
}
