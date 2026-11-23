<?php

namespace App\Filament\Operations\Resources\Complaints\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ComplaintsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')

            ->columns([

                TextColumn::make('id')
                    ->label('#')
                    ->rowIndex(),

                TextColumn::make('customer.customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('agent.name')
                    ->label('Sales Executive')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.category.name')
                    ->label('Category')
                    ->badge()
                    ->searchable(),

                TextColumn::make('customer.machineModel.name')
                    ->label('Machine Model')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.attachments')
                    ->label('Attachments')
                    ->state(function ($record) {
                        $attachments = $record->customer?->attachments;

                        if (blank($attachments)) {
                            return [];
                        }

                        return \App\Models\CategoryAttachment::whereIn('id', $attachments)
                            ->pluck('title')
                            ->toArray();
                    })
                    ->badge(),

                TextColumn::make('complaint')
                    ->label('Complaint')
                    ->limit(60)
                    ->searchable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'pending' => 'warning',
                        'in_progress' => 'info',
                        'resolved' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Raised On')
                    ->dateTime('d M Y h:i A')
                    ->sortable(),

            ])

            ->filters([

                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'resolved' => 'Resolved',
                    ]),

            ])

            ->recordActions([
                EditAction::make()
                    ->label('Reply'),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}