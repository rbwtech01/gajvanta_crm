<?php

namespace App\Filament\Resources\CallCenters\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Http;

class CallCenterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Textarea::make('address')
                    ->rows(3)
                    ->columnSpanFull(),

                TextInput::make('pincode')
                    ->label('Pincode')
                    ->required()
                    ->minLength(6)
                    ->maxLength(6)
                    ->numeric()
                    ->live(debounce: 700)
                    ->afterStateUpdated(function (?string $state, Set $set) {
                        if (strlen((string) $state) !== 6) {
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

                            $set('city', $office['District']);
                            $set('state', $office['State']);
                        }
                    }),

                TextInput::make('city')
                    ->label('District')
                    ->readOnly(),

                TextInput::make('state')
                    ->readOnly(),

                Toggle::make('is_active')
                    ->default(true),

            ]);
    }
}