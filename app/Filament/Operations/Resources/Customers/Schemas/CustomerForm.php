<?php

namespace App\Filament\Operations\Resources\Customers\Schemas;

use App\Models\CategoryAttachment;
use App\Models\HoldReason;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // Customer Information
                Section::make('Customer Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('customer_name')
                            ->readOnly(),

                        TextInput::make('mobile')
                            ->readOnly(),

                        Textarea::make('address')
                            ->readOnly()
                            ->columnSpanFull(),

                        TextInput::make('pincode')
                            ->readOnly(),

                        TextInput::make('district')
                            ->readOnly(),

                        TextInput::make('state')
                            ->readOnly(),
                    ]),

                // Machine Information
                Section::make('Machine Information')
                    ->columns(2)
                    ->schema([
                        Select::make('category_id')
                            ->relationship('category', 'name')
                            ->disabled(),

                        Select::make('machine_model_id')
                            ->relationship('machineModel', 'name')
                            ->disabled(),

                        TextEntry::make('attachments')
                            ->label('Attachments')
                            ->state(function ($record) {
                                if (blank($record->attachments)) {
                                    return [];
                                }

                                return CategoryAttachment::whereIn(
                                    'id',
                                    $record->attachments
                                )
                                    ->pluck('title')
                                    ->toArray();
                            })
                            ->badge(),
                    ]),

                // Operations
                // Operations
                Section::make('Operations')
                    ->columns(2)
                    ->schema([

                        Select::make('status')
                            ->options(fn ($record) => $record?->status === 'confirmed'
                                ? [
                                    'confirmed' => 'Confirmed',
                                ]
                                : [
                                    'hold' => 'Hold',
                                    'processing' => 'Processing',
                                ]
                            )
                            ->disabled(fn ($record): bool => $record?->status === 'confirmed')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (?string $state, Set $set): void {
                                if ($state === 'processing') {
                                    $set('hold_reason_id', null);
                                    $set('hold_remark', null);
                                    $set('delivery_date', null);
                                }

                                if ($state === 'hold') {
                                    $set('bill_number', null);
                                }
                            }),

                        // Hold Reason
                        Select::make('hold_reason_id')
                            ->label('Hold Reason')
                            ->relationship('holdReason', 'reason')
                            ->visible(fn (Get $get): bool => $get('status') === 'hold'
                            )
                            ->required(fn (Get $get): bool => $get('status') === 'hold'
                            )
                            ->live(),

                        DatePicker::make('delivery_date')
                            ->label('Delivery Schedule Date')
                            ->native(false)
                            ->visible(function (Get $get): bool {
                                if ($get('status') !== 'hold') {
                                    return false;
                                }

                                $holdReasonId = $get('hold_reason_id');

                                if (! $holdReasonId) {
                                    return false;
                                }

                                return HoldReason::whereKey($holdReasonId)
                                    ->where('reason', 'Ordered & Delivery Scheduled')
                                    ->exists();
                            })
                            ->required(function (Get $get): bool {
                                if ($get('status') !== 'hold') {
                                    return false;
                                }

                                $holdReasonId = $get('hold_reason_id');

                                if (! $holdReasonId) {
                                    return false;
                                }

                                return HoldReason::whereKey($holdReasonId)
                                    ->where('reason', 'Ordered & Delivery Scheduled')
                                    ->exists();
                            }),

                        // Hold Remark
                        Textarea::make('hold_remark')
                            ->label('Remark')
                            ->visible(fn (Get $get): bool => $get('status') === 'hold' &&
                                filled($get('hold_reason_id'))
                            )
                            ->required(fn (Get $get): bool => $get('status') === 'hold' &&
                                filled($get('hold_reason_id'))
                            )
                            ->columnSpanFull(),

                        // Bill Number
                        TextInput::make('bill_number')
                        ->label('Bill Number')
                        ->visible(fn (Get $get): bool => $get('status') === 'processing')
                        ->nullable()
                        ->rules([
                            'regex:/^\d+$/',
                            'digits_between:4,6',
                        ])
                        ->validationMessages([
                            'regex' => 'Only digits are allowed.',
                            'digits_between' => 'Bill Number must be 4 to 6 digits.',
                        ]),
                    ]),

                // Payment Details
                Section::make('Payment Details')
                    ->columns(2)    
                    ->schema([
                        TextInput::make('price')
                            ->prefix('₹')
                            ->readOnly(),

                        TextInput::make('advance_payment')
                            ->prefix('₹')
                            ->readOnly(),
                    ]),
            ]);
    }
}
