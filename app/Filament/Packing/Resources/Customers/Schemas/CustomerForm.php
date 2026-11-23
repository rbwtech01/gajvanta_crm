<?php

namespace App\Filament\Packing\Resources\Customers\Schemas;

use App\Models\CategoryAttachment;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                /*
                |--------------------------------------------------------------------------
                | Customer Information
                |--------------------------------------------------------------------------
                */

                Section::make('Customer Information')
                    ->columns(2)
                    ->schema([

                        TextInput::make('customer_name')
                            ->label('Customer')
                            ->readOnly()
                            ->dehydrated(false),

                        TextInput::make('mobile')
                            ->label('Mobile')
                            ->readOnly()
                            ->dehydrated(false),

                        TextInput::make('agent_display')
                            ->label('Sales Executive')
                            ->formatStateUsing(
                                fn ($record) => $record?->agent?->name
                            )
                            ->readOnly()
                            ->dehydrated(false),

                        TextInput::make('booking_date')
                            ->label('Booking Date')
                            ->formatStateUsing(
                                fn ($state) => $state
                                    ? \Carbon\Carbon::parse($state)->format('d/m/Y')
                                    : null
                            )
                            ->readOnly()
                            ->dehydrated(false),

                    ]),

                /*
                |--------------------------------------------------------------------------
                | Machine Details
                |--------------------------------------------------------------------------
                */

                Section::make('Machine Details')
                    ->columns(2)
                    ->schema([

                        TextInput::make('category_display')
                            ->label('Category')
                            ->formatStateUsing(
                                fn ($record) => $record?->category?->name
                            )
                            ->readOnly()
                            ->dehydrated(false),

                        TextInput::make('machine_model_display')
                            ->label('Machine Model')
                            ->formatStateUsing(
                                fn ($record) => $record?->machineModel?->name
                            )
                            ->readOnly()
                            ->dehydrated(false),

                        TextInput::make('price')
                            ->label('Price')
                            ->prefix('₹')
                            ->readOnly()
                            ->dehydrated(false),

                        TextInput::make('advance_payment')
                            ->label('Advance Payment')
                            ->prefix('₹')
                            ->readOnly()
                            ->dehydrated(false),

                        TextInput::make('attachments_display')
                            ->label('Attachments')
                            ->formatStateUsing(function ($record) {

                                if (blank($record?->attachments)) {
                                    return 'No attachments';
                                }

                                return CategoryAttachment::whereIn(
                                    'id',
                                    $record->attachments
                                )
                                    ->pluck('title')
                                    ->join(', ');
                            })
                            ->readOnly()
                            ->dehydrated(false)
                            ->columnSpanFull(),

                    ]),

                /*
                |--------------------------------------------------------------------------
                | Order Status
                |--------------------------------------------------------------------------
                */

                Section::make('Order Status')
                    ->schema([

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'packed' => 'Packed',
                            ])
                            ->disableOptionWhen(
                                fn (string $value): bool => $value === 'processing'
                            )
                            ->required(),

                    ]),
            ]);
    }
}