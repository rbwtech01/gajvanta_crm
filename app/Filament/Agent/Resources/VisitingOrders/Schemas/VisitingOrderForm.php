<?php

namespace App\Filament\Agent\Resources\VisitingOrders\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Http;

class VisitingOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Visitor Details')
                    ->schema([
                        TextInput::make('visitor_name')
                            ->label('Visitor Name')
                            ->required()
                            ->maxLength(255)
                            ->disabled(
                                fn (string $operation): bool =>
                                    $operation === 'edit'
                            ),
                            

                        TextInput::make('primary_mobile')
                            ->label('Primary Mobile')
                            ->tel()
                            ->required()
                            ->maxLength(15)
                            ->disabled(
                                fn (string $operation): bool =>
                                    $operation === 'edit'
                            ),

                        TextInput::make('secondary_mobile')
                            ->label('Secondary Mobile')
                            ->tel()
                            ->maxLength(15)
                            ->disabled(
                                fn (string $operation): bool =>
                                    $operation === 'edit'
                            ),

                        TextInput::make('pincode')
                            ->label('Pincode')
                            ->required()
                            ->maxLength(6)
                            ->minLength(6)
                            ->numeric()
                            ->live(debounce: 700)
                            ->disabled(
                                fn (string $operation): bool =>
                                    $operation === 'edit'
                            )
                            ->afterStateUpdated(
                                function (
                                    ?string $state,
                                    Set $set
                                ): void {
                                    if (strlen((string) $state) !== 6) {
                                        return;
                                    }

                                    $response = Http::withHeaders([
                                        'User-Agent' => 'Mozilla/5.0 '
                                            . '(Windows NT 10.0; Win64; x64) '
                                            . 'AppleWebKit/537.36 '
                                            . '(KHTML, like Gecko) '
                                            . 'Chrome/124.0.0.0 '
                                            . 'Safari/537.36',
                                    ])->get(
                                        "https://api.postalpincode.in/pincode/{$state}"
                                    );

                                    if (! $response->successful()) {
                                        return;
                                    }

                                    $data = $response->json();

                                    if (
                                        isset($data[0]['Status']) &&
                                        $data[0]['Status'] === 'Success' &&
                                        ! empty($data[0]['PostOffice'][0])
                                    ) {
                                        $office =
                                            $data[0]['PostOffice'][0];

                                        $set(
                                            'city',
                                            $office['District'] ?? ''
                                        );

                                        $set(
                                            'state',
                                            $office['State'] ?? ''
                                        );
                                    }
                                }
                            ),

                        TextInput::make('city')
                            ->label('City / District')
                            ->required()
                            ->maxLength(255)
                            ->disabled(
                                fn (string $operation): bool =>
                                    $operation === 'edit'
                            )
                            ->readOnly(),

                        TextInput::make('state')
                            ->label('State')
                            ->required()
                            ->maxLength(255)
                            ->disabled(
                                fn (string $operation): bool =>
                                    $operation === 'edit'
                            )
                            ->readOnly(),

                        DatePicker::make('visit_date')
                        ->label('Visit Date')
                        ->required()
                        ->native(false)
                        ->minDate(function (?string $operation, $record) {
                            if ($operation === 'edit' && $record) {
                                return $record->created_at->copy()->startOfDay();
                            }

                            return today();
                        })
                        ->maxDate(function (?string $operation, $record) {
                            if ($operation === 'edit' && $record) {
                                return $record->created_at
                                    ->copy()
                                    ->addDays(7)
                                    ->startOfDay();
                            }

                            return today()->addDays(7);
                        }),

                        Textarea::make('remark')
                            ->label('Remark')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull()
                            ->disabled(
                                fn (string $operation): bool =>
                                    $operation === 'edit'
                            ),
                    ])
                    ->columns(2),
            ]);
    }
}