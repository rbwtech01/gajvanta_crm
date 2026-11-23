<?php

namespace App\Filament\Agent\Resources\Customers\Tables;

use App\Exports\CustomersExport;
use App\Models\CategoryAttachment;
use App\Models\Complaint;
use App\Services\CustomerImportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query) => $query->whereIn('status', [
                    'created',
                    'supervisor_hold',
                    'supervisor_confirmed',
                    'hold',
                    'dispatched',
                    'delivered',
                    'processing',
                    'packed',
                    'confirmed',
                ])
            )
            ->recordUrl(null)
            ->recordAction(null)
            ->columns([
                TextColumn::make('sr_no')
                    ->label('#')
                    ->state(
                        static fn ($record, $livewire) => $livewire->getTableRecords()->search($record) + 1),

                ImageColumn::make('payment_proof')
                    ->getStateUsing(
                        fn ($record) => $record->getFirstMediaUrl('screenshots')
                    )
                    ->defaultImageUrl('https://placehold.co/100x100/png?text=No+Image')
                    ->circular()
                    ->size(50),

                TextColumn::make('customer_name')
                    ->searchable(),

                TextColumn::make('booking_date')
                    ->label('Booking Date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('mobile')
                    ->searchable(),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),

                TextColumn::make('machineModel.name')
                    ->label('Machine Model'),

                TextColumn::make('attachments')
                    ->label('Attachments')
                    ->badge()
                    ->state(function ($record) {

                        if (blank($record->attachments)) {
                            return [];
                        }

                        return CategoryAttachment::whereIn('id', $record->attachments)
                            ->pluck('title')
                            ->toArray();
                    }),

                TextColumn::make('quantity')
                    ->label('Quantity')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('price')
                    ->money('INR'),

                TextColumn::make('advance_payment')
                    ->money('INR'),

                TextColumn::make('status')
                    ->badge(),

                TextColumn::make('holdReason.reason')
                    ->label('Hold Reason')
                    ->placeholder('No Reason')
                    ->wrap(),

                TextColumn::make('hold_remark')
                    ->label('Hold Remark')
                    ->placeholder('No Remark')
                    ->wrap(),

                TextColumn::make('bill_number')
                    ->label('Bill Number')
                    ->placeholder('Not Filled')
                    ->copyable()
                    ->searchable(),

                TextColumn::make('shipment.tracking_id')
                    ->label('Tracking ID')
                    ->placeholder('Not Available')
                    ->searchable(),

                TextColumn::make('district'),

                TextColumn::make('state'),

                TextColumn::make('booking_date')
                    ->label('Booking Date')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')

            ->headerActions([

                Action::make('import_customers')
                    ->label('Import Customers')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('success')
                    ->schema([
                        FileUpload::make('file')
                            ->label('Excel File')
                            ->disk('local')
                            ->directory('customer-imports')
                            ->acceptedFileTypes([
                                'text/csv',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            ])
                            ->required(),
                    ])
                    ->action(function (array $data) {

                        $agentId = auth()->id();

                        if (! $agentId) {
                            throw new \RuntimeException(
                                'Agent is not authenticated.'
                            );
                        }

                        $filePath = Storage::disk('local')
                            ->path($data['file']);

                        $customers = app(CustomerImportService::class)->import(
                            filePath: $filePath,
                            agentId: $agentId,
                        );

                        Notification::make()
                            ->title('Import completed')
                            ->body(
                                "{$customers->count()} customer(s) imported successfully."
                            )
                            ->success()
                            ->send();
                    }),

                Action::make('export_excel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        return Excel::download(
                            new CustomersExport(auth()->id()),
                            'customers.xlsx'
                        );
                    }),
            ])
            ->filters([

                // TrashedFilter::make(),

                Filter::make('today')
                    ->query(fn (Builder $query) => $query->whereDate('created_at', today())
                    ),

                Filter::make('this_week')
                    ->label('This Week')
                    ->query(fn (Builder $query) => $query->whereBetween(
                        'created_at',
                        [now()->startOfWeek(), now()->endOfWeek()]
                    )
                    ),

                Filter::make('this_month')
                    ->label('This Month')
                    ->query(fn (Builder $query) => $query->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)
                    ),

                Filter::make('last_month')
                    ->label('Last Month')
                    ->query(fn (Builder $query) => $query->whereBetween(
                        'created_at',
                        [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()]
                    )),

                Filter::make('booking_date_range')
                    ->label('Booking Date Range')
                    ->schema([
                        DatePicker::make('from_date')
                            ->label('From')
                            ->native(false),

                        DatePicker::make('to_date')
                            ->label('To')
                            ->native(false),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(
                            $data['from_date'] ?? null,
                            fn (Builder $query, $date): Builder => $query->whereDate('booking_date', '>=', $date)
                        )
                        ->when(
                            $data['to_date'] ?? null,
                            fn (Builder $query, $date): Builder => $query->whereDate('booking_date', '<=', $date)
                        )),
            ])
            ->recordActions([
                ViewAction::make('shipment')
                    ->label('Track')
                    ->modalHeading('Shipment Details')
                    ->modalContent(fn ($record) => view(
                            'filament.shipment.shipment-timeline',
                            ['record' => $record]
                        )),
                EditAction::make(),
                Action::make('addComplaint')
                    ->label('Add Complaint')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('warning')
                    ->visible(fn ($record) => $record->complaints()->doesntExist())
                    ->modalHeading('Add Complaint')
                    ->schema([
                        Textarea::make('complaint')
                            ->label('Complaint')
                            ->rows(5)
                            ->required(),
                    ])
                    ->action(function (array $data, $record) {

                        Complaint::create([
                            'customer_id' => $record->id,
                            'agent_id' => auth()->id(),
                            'complaint' => $data['complaint'],
                            'status' => 'pending',
                        ]);

                        Notification::make()
                            ->title('Complaint submitted successfully.')
                            ->success()
                            ->send();
                    }),
                Action::make('viewComplaint')
                    ->label('View Complaint')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('info')
                    ->visible(fn ($record) => $record->complaints()->exists())
                    ->modalHeading('Complaint Details')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->schema([
                        Placeholder::make('complaint')
                            ->label('Complaint')
                            ->content(fn ($record) => optional($record->complaints()->latest()->first())->complaint ?? '-'),

                        Placeholder::make('reply')
                            ->label('Reply')
                            ->content(fn ($record) => optional($record->complaints()->latest()->first())->reply ?? 'No reply yet'),

                        Placeholder::make('status')
                            ->label('Status')
                            ->content(fn ($record) => ucfirst(optional($record->complaints()->latest()->first())->status ?? 'pending')),
                    ]),
                Action::make('generate_invoice')
                    ->label('Invoice')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->action(function ($record) {

                        $invoiceNo = 'RBW/'.random_int(1000, 999999);

                        $pdf = Pdf::loadView('pdf.invoice', [
                            'customer' => $record,
                            'attachments' => CategoryAttachment::whereIn(
                                'id',
                                $record->attachments ?? []
                            )->pluck('title')->toArray(),
                            'balance' => $record->price - $record->advance_payment,
                            'invoiceNo' => $invoiceNo,
                        ]);

                        return response()->streamDownload(
                            fn () => print ($pdf->output()),
                            'Invoice-'.Str::slug($record->customer_name).'.pdf',
                            ['Content-Type' => 'application/pdf']
                        );

                    }),
                // DeleteAction::make(),
                // RestoreAction::make(),
                // ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                //     RestoreBulkAction::make(),
                //     ForceDeleteBulkAction::make(),
                // ]),
            ]);
    }
}
