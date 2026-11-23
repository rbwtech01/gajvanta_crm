<?php

namespace App\Imports;

use App\Models\MetaAdReport;
use App\Models\User;
use Carbon\Carbon;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

class MetaAdsImport implements ToCollection, WithHeadingRow
{
    public function __construct(
        private readonly ?string $uploadId = null
    ) {}

    public array $unmappedCampaigns = [];

    public int $processedRows = 0;

    public int $insertedRows = 0;

    public int $duplicateRows = 0;

    public int $invalidRows = 0;

    /**
     * Import Excel rows.
     */
    public function collection(Collection $collection): void
    {
        Log::info('Meta Ads import rows loaded', [
            'upload_id' => $this->uploadId,
            'row_count' => $collection->count(),
            'headers' => $collection->first() instanceof Collection
                ? $collection->first()->keys()->all()
                : [],
        ]);

        foreach ($collection as $rowIndex => $row) {
            $this->processedRows++;

            // Heading is Excel row 1; first data row is row 2.
            $excelRowNumber = $rowIndex + 2;

            try {
                $this->importRow($row, $excelRowNumber);
            } catch (Throwable $exception) {
                $this->invalidRows++;

                Log::error('Meta Ads row import failed', [
                    'upload_id' => $this->uploadId,
                    'excel_row' => $excelRowNumber,
                    'exception' => $exception,
                ]);
            }
        }
    }

    /**
     * Validate and import one row.
     */
    protected function importRow(
        Collection $row,
        int $excelRowNumber
    ): void {
        /*
         * 1. Validate campaign name.
         */
        $campaignName = trim((string) $this->valueFromRow(
            $row,
            ['campaign_name', 'ad_name', 'campaign']
        ));

        if ($campaignName === '') {
            $this->skipInvalidRow(
                $excelRowNumber,
                'Campaign name is missing',
                ['available_columns' => $row->keys()->all()]
            );

            return;
        }

        /*
         * 2. Extract the first word as the Agent's first name.
         *
         * Example:
         * Gagan 26AUG R-505 AI2
         *
         * Extracted:
         * Gagan
         */
        $parts = preg_split('/\s+/u', $campaignName);

        $agentName = trim($parts[0] ?? '');

        if ($agentName === '') {
            $this->skipInvalidRow(
                $excelRowNumber,
                'Agent name could not be extracted',
                ['campaign_name' => $campaignName]
            );

            return;
        }

        /*
         * 3. Match the CRM Agent.
         *
         * Only active users with the Agent role qualify.
         * Match the first word of the CRM name, ignoring case.
         */
        $matchingAgents = User::query()
            ->role('Agent')
            ->where('is_active', true)
            ->whereRaw(
                "LOWER(TRIM(SUBSTRING_INDEX(name, ' ', 1))) = ?",
                [mb_strtolower($agentName)]
            )
            ->get();

        if ($matchingAgents->count() !== 1) {
            $reason = $matchingAgents->isEmpty()
                ? 'Agent first name not found'
                : 'Multiple agents have the same first name';

            $this->unmappedCampaigns[] = [
                'excel_row' => $excelRowNumber,
                'campaign_name' => $campaignName,
                'agent_name' => $agentName,
                'reason' => $reason,
            ];

            Log::warning('Meta Ads campaign could not be mapped to an agent', [
                'upload_id' => $this->uploadId,
                'excel_row' => $excelRowNumber,
                'campaign_name' => $campaignName,
                'agent_name' => $agentName,
                'reason' => $reason,
            ]);

            return;
        }

        $agent = $matchingAgents->first();

        /*
         * 4. Parse report date.
         */
        $reportDate = $this->parseDate(
            $this->valueFromRow($row, ['date', 'report_date', 'reporting_date'])
        );

        if ($reportDate === null) {
            $this->skipInvalidRow(
                $excelRowNumber,
                'Invalid or missing report date',
                [
                    'campaign_name' => $campaignName,
                    'available_columns' => $row->keys()->all(),
                ]
            );

            return;
        }

        /*
         * 5. Parse budget and amount spent.
         *
         * Blank cells are treated as zero to preserve the
         * previous import behavior.
         *
         * Invalid nonblank values return null and are rejected.
         */
        $budget = $this->parseAmount(
            $this->valueFromRow(
                $row,
                ['budget', 'ad_set_budget', 'adset_budget']
            )
        );

        $amountSpent = $this->parseAmount(
            $this->valueFromRow(
                $row,
                ['amount_spent', 'amount_spent_inr', 'spend']
            )
        );

        if ($budget === null || $amountSpent === null) {
            $this->skipInvalidRow(
                $excelRowNumber,
                'Invalid budget or amount spent',
                [
                    'campaign_name' => $campaignName,
                    'available_columns' => $row->keys()->all(),
                ]
            );

            return;
        }

        /*
         * 6. Remove the first word from the campaign name.
         *
         * Original:
         * Gagan 26AUG R-505 AI2
         *
         * Stored:
         * 26AUG R-505 AI2
         */
        $displayCampaignName = trim(
            (string) preg_replace(
                '/^\S+\s+/u',
                '',
                $campaignName,
                1
            )
        );

        if ($displayCampaignName === '') {
            $this->skipInvalidRow(
                $excelRowNumber,
                'Campaign name is empty after removing Agent name',
                ['campaign_name' => $campaignName]
            );

            return;
        }

        /*
         * 7. Check for exact duplicates and insert.
         *
         * All five identity fields must match:
         * Agent ID, report date, campaign name,
         * amount spent, and budget.
         */
        $newReport = DB::transaction(function () use (
            $agent,
            $reportDate,
            $displayCampaignName,
            $budget,
            $amountSpent

        ): ?MetaAdReport {
            $existingReport = MetaAdReport::query()
                ->where('agent_id', $agent->id)
                ->whereDate('report_date', $reportDate)
                ->where('campaign_name', $displayCampaignName)
                ->where('amount_spent', $amountSpent)
                ->where('budget', $budget)
                ->first();

            if ($existingReport !== null) {
                return null;
            }

            return MetaAdReport::create([
                'agent_id' => $agent->id,
                'report_date' => $reportDate,
                'campaign_name' => $displayCampaignName,
                'budget' => $budget,
                'amount_spent' => $amountSpent,
            ]);
        });

        if ($newReport === null) {
            $this->duplicateRows++;

            Log::info('Meta Ads row skipped as duplicate', [
                'upload_id' => $this->uploadId,
                'excel_row' => $excelRowNumber,
                'agent_id' => $agent->id,
                'report_date' => $reportDate,
                'campaign_name' => $displayCampaignName,
            ]);

            return;
        }

        $this->insertedRows++;

        Log::info('Meta Ads row imported', [
            'upload_id' => $this->uploadId,
            'excel_row' => $excelRowNumber,
            'report_id' => $newReport->id,
            'agent_id' => $agent->id,
            'report_date' => $reportDate,
        ]);
    }

    /**
     * Record an invalid row and continue the import.
     */
    protected function skipInvalidRow(
        int $excelRowNumber,
        string $reason,
        array $context = []
    ): void {
        $this->invalidRows++;

        Log::warning('Meta Ads row skipped', [
            'upload_id' => $this->uploadId,
            'excel_row' => $excelRowNumber,
            'reason' => $reason,
            ...$context,
        ]);
    }

    private function valueFromRow(Collection $row, array $columns): mixed
    {
        foreach ($columns as $column) {
            if ($row->has($column) && $row->get($column) !== null) {
                return $row->get($column);
            }
        }

        return null;
    }

    /**
     * Parse an Excel date into Y-m-d.
     *
     * Supported string formats:
     * Y-m-d, d-m-Y, d/m/Y, d.m.Y
     */
    protected function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value)->format('Y-m-d');
        }

        /*
         * Excel numeric serial date.
         */
        if (is_numeric($value)) {
            try {
                $date = ExcelDate::excelToDateTimeObject($value);

                return $date->format('Y-m-d');
            } catch (Throwable $exception) {

                return null;
            }
        }

        /*
         * Strictly validate supported date string formats.
         */
        $value = trim((string) $value);

        $formats = [
            '!Y-m-d',
            '!d-m-Y',
            '!d/m/Y',
            '!d.m.Y',
        ];

        foreach ($formats as $format) {
            $date = DateTimeImmutable::createFromFormat(
                $format,
                $value
            );

            $errors = DateTimeImmutable::getLastErrors();

            $hasErrors = $errors !== false
                && (
                    $errors['warning_count'] > 0
                    || $errors['error_count'] > 0
                );

            if (
                $date !== false
                && ! $hasErrors
                && $date->format(substr($format, 1)) === $value
            ) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    /**
     * Parse an amount into a numeric value.
     *
     * Supported examples:
     * ₹527.51
     * ₹ 1,527.51
     * Rs. 1527.51
     * 1,527.51
     * 1527.51
     *
     * Returns:
     * float = valid amount
     * 0.00  = blank value, preserving existing behavior
     * null  = invalid nonblank value
     */
    protected function parseAmount(mixed $value): ?float
    {
        if ($value === null || trim((string) $value) === '') {
            return 0.00;
        }

        /*
         * Excel may already provide a numeric value.
         */
        if (is_numeric($value)) {
            $amount = (float) $value;

            if (! is_finite($amount)) {
                return null;
            }

            return round($amount, 2);
        }

        $value = trim((string) $value);

        /*
         * Remove supported currency prefixes.
         */
        $value = preg_replace(
            '/^(?:₹|Rs\.?|INR)\s*/iu',
            '',
            $value
        );

        /*
         * Remove thousands separators and whitespace.
         */
        $value = str_replace(',', '', $value);
        $value = preg_replace('/\s+/u', '', $value);

        /*
         * Reject unexpected characters rather than silently
         * converting invalid input into a legitimate amount.
         */
        if (
            $value === ''
            || ! preg_match('/^-?\d+(?:\.\d+)?$/', $value)
        ) {
            return null;
        }

        $amount = (float) $value;

        if (! is_finite($amount)) {
            return null;
        }

        return round($amount, 2);
    }
}
