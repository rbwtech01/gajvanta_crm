<?php

namespace App\Filament\Supervisor\Resources\Customers\Schemas;

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
