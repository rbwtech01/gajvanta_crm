<?php

namespace App\Filament\Shipment\Resources\Customers\Schemas;

use App\Models\CategoryAttachment;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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
                | Order Information
                |--------------------------------------------------------------------------
                */

                Section::make('Order Information')
                    ->columns(2)
                    ->schema([

                        TextInput::make('customer_name')
                            ->label('Customer')
                            ->readOnly(),

                        TextInput::make('mobile')
                            ->label('Mobile')
                            ->readOnly(),

                        TextInput::make('booking_date')
                            ->label('Booking Date')
                            ->readOnly(),

                        TextInput::make('agent_display')
                            ->label('Sales Executive')
                            ->formatStateUsing(
                                fn ($record) => $record?->agent?->name
                            )
                            ->readOnly()
                            ->dehydrated(false),

                        TextInput::make('call_center_display')
                            ->label('Call Center')
                            ->formatStateUsing(
                                fn ($record) => $record?->agent?->callCenter?->name
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
                            ->readOnly(),

                        TextInput::make('advance_payment')
                            ->label('Advance Payment')
                            ->prefix('₹')
                            ->readOnly(),

                        TextInput::make('attachments_display')
                            ->label('Attachments')
                            ->formatStateUsing(function ($record) {

                                if (blank($record?->attachments)) {
                                    return null;
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
                | Location
                |--------------------------------------------------------------------------
                */

                Section::make('Location')
                    ->columns(2)
                    ->schema([

                        TextInput::make('district')
                            ->label('District')
                            ->readOnly(),

                        TextInput::make('state')
                            ->label('State')
                            ->readOnly(),
                    ]),

                /*
                |--------------------------------------------------------------------------
                | Shipment Details
                |--------------------------------------------------------------------------
                */

                Section::make('Shipment Details')
                    ->relationship('shipment')
                    ->columns(2)
                    ->schema([
                        Select::make('courier_id')
                            ->label('Courier Name')
                            ->relationship('courier', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('tracking_id')
                            ->label('Tracking ID')
                            ->required(),

                        Textarea::make('remarks')
                            ->label('Remarks')
                            ->rows(4)
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
                                'delivered' => 'Delivered',
                            ])
                            ->required()
                            ->disabled(fn ($record) => $record?->status === 'delivered'),
                    ])
                    ->visible(function ($record) {
                        return in_array($record?->status, [
                            'dispatched',
                            'delivered',
                        ]);
                    }),
            ]);
    }
}
