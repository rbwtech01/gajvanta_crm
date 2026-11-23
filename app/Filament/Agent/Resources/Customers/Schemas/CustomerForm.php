<?php

namespace App\Filament\Agent\Resources\Customers\Schemas;

use App\Models\Category;
use App\Models\CategoryAttachment;
use App\Models\MachineModel;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([

                Hidden::make('user_id')->default(fn () => Auth::id()),

                TextInput::make('customer_name')
                    ->label('Customer Name')
                    ->hiddenOn('edit')
                    ->required(),

                TextInput::make('mobile')
                    ->label('Mobile Number')
                    ->tel()
                    ->hiddenOn('edit')
                    ->required(),

                Select::make('category_id')
                    ->label('Category')
                    ->options(Category::query()->pluck('name', 'id')->all())
                    ->live()
                    ->hiddenOn('edit')
                    ->searchable()
                    ->preload()
                    ->afterStateUpdated(function ($state, Set $set) {
                        logger()->info([
                            'Selected Category' => $state,
                        ]);

                        $set('machine_model_id', null);
                        $set('attachments', []);
                    })
                    ->required(),

                Select::make('machine_model_id')
                    ->label('Machine Model')
                    ->required()
                    ->hiddenOn('edit')
                    ->options(function (Get $get) {
                        return MachineModel::where(
                            'category_id',
                            $get('category_id')
                        )->pluck('name', 'id')->all();
                    })
                    ->live()
                    ->searchable(),

                Select::make('attachments')
                    ->label('Attachments')
                    ->required()
                    ->hiddenOn('edit')
                    ->options(function (Get $get) {
                        return CategoryAttachment::where('category_id', $get('category_id'))
                            ->pluck('title', 'id')
                            ->toArray();
                    })
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        logger()->info('Attachments Selected', [
                            'attachments' => $state,
                        ]);
                    }),

                TextInput::make('quantity')
                    ->label('Quantity')
                    ->integer()
                    ->default(1)
                    ->minValue(1)
                    ->required()
                    ->hiddenOn('edit'),

                TextInput::make('price')
                    ->hiddenOn('edit')
                    ->prefix('₹')
                    ->required(),

                TextInput::make('advance_payment')
                    ->label('Advance Payment')
                    ->hiddenOn('edit')
                    ->required()
                    ->prefix('₹'),

                TextInput::make('pincode')
                    ->label('Pincode')
                    ->required()
                    ->hiddenOn('edit')
                    ->minLength(6)
                    ->maxLength(6)
                    ->live(debounce: 700)
                    ->afterStateUpdated(function (?string $state, Set $set) {

                        if (strlen($state) !== 6) {
                            return;
                        }

                        $response = Http::withHeaders([
                            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
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
                    ->required()
                    ->hiddenOn('edit')
                    ->readOnly(),

                TextInput::make('state')
                    ->required()
                    ->hiddenOn('edit')
                    ->readOnly(),

                Textarea::make('address')
                    ->required()
                    ->hiddenOn('edit')
                    ->rows(2),

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
