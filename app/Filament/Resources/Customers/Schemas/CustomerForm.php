<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Models\MachineModel;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\CategoryAttachment;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([

                Hidden::make('user_id')
                    ->default(fn () => Auth::id()),

                TextInput::make('customer_name')
                    ->label('Customer Name')
                    ->required(),

                TextInput::make('mobile')
                    ->label('Mobile Number')
                    ->tel()
                    ->required(),

                Select::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (Set $set) {
                        $set('machine_model_id', null);
                        $set('attachments', []);
                    })
                    ->required(),

                Select::make('machine_model_id')
                    ->label('Machine Model')
                    ->options(function (Get $get) {
                        return MachineModel::query()
                            ->where('category_id', $get('category_id'))
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (Set $set) {
                        $set('attachments', []);
                    })
                    ->required(),

                Select::make('attachments')
                    ->label('Attachments')
                    ->options(function (Get $get) {
                        return CategoryAttachment::query()
                            ->where('category_id', $get('category_id'))
                            ->where('status', true)
                            ->pluck('title', 'id')
                            ->toArray();
                    })
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->hidden(fn (Get $get) => blank($get('category_id')))
                    ->required(),

                TextInput::make('price')
                    ->numeric()
                    ->prefix('₹')
                    ->required(),

                TextInput::make('advance_payment')
                    ->label('Advance Payment')
                    ->numeric()
                    ->prefix('₹')
                    ->default(0),

                Select::make('status')
                    ->options([
                        'created' => 'Created',
                        'hold' => 'Hold',
                        'dispatched' => 'Dispatched',
                        'delivered' => 'Delivered',
                    ])
                    ->default('created')
                    ->required(),

                TextInput::make('pincode')
                    ->label('Pincode')
                    ->required()
                    ->numeric()
                    ->minLength(6)
                    ->maxLength(6)
                    ->live(debounce: 700)
                    ->afterStateUpdated(function (?string $state, Set $set) {

                        if (strlen((string) $state) !== 6) {
                            return;
                        }

                        $response = Http::withHeaders([
                            'User-Agent' => 'Mozilla/5.0',
                        ])->get("https://api.postalpincode.in/pincode/{$state}");

                        if (! $response->successful()) {
                            return;
                        }

                        $data = $response->json();

                        if (
                            isset($data[0]['Status']) &&
                            $data[0]['Status'] === 'Success'
                        ) {
                            $office = $data[0]['PostOffice'][0];

                            $set('district', $office['District']);
                            $set('state', $office['State']);
                        }
                    }),

                TextInput::make('district')
                    ->readOnly()
                    ->required(),

                TextInput::make('state')
                    ->readOnly()
                    ->required(),

                Textarea::make('address')
                    ->rows(2)
                    ->required(),

                DatePicker::make('booking_date')
                    ->label('Booking Date')
                    ->default(now())
                    ->displayFormat('d/m/Y')
                    ->native(false)
                    ->required(),

                // TimePicker::make('booking_time')
                //     ->label('Booking Time')
                //     ->default(now())
                //     ->seconds(false)
                //     ->native(false)
                //     ->required(),

                SpatieMediaLibraryFileUpload::make('screenshots')
                    ->collection('screenshots')
                    ->image(),
            ]);
    }
}