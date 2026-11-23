<?php

namespace App\Exports;

use App\Models\MachinerySerial;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MachinerySerialsExport implements FromCollection, WithHeadings, WithStyles
{
    protected int $machineryItemId;

    protected int $limit;

    public function __construct(int $machineryItemId, int $limit)
    {
        $this->machineryItemId = $machineryItemId;
        $this->limit = $limit;
    }

    public function collection(): Collection
    {
        return MachinerySerial::query()
            ->with([
                'machineryItem.category',
            ])
            ->where('machinery_item_id', $this->machineryItemId)
            ->orderBy('id')
            ->limit($this->limit)
            ->get()
            ->map(function ($serial) {

                $item = $serial->machineryItem;

                return [
                    $item?->category?->name ?? 'N/A',
                    $item?->name ?? 'N/A',
                    $item?->model_code ?? 'N/A',
                    $item?->model_tag ?? 'N/A',
                    $item?->dealer_price ?? 'N/A',
                    $serial->serial_number,
                    $serial->serial_code,
                    $serial->is_active ? 'Yes' : 'No',
                    $serial->created_at?->format('M d, Y H:i:s'),
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Category',
            'Model Name',
            'Model Code',
            'Model Tag',
            'Dealer Price',
            'Serial Number',
            'Serial Code',
            'Active',
            'Generated At',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                ],
            ],
        ];
    }
}