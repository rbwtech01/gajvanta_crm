<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUser;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Agent';

    protected static ?string $pluralModelLabel = 'Agents';

    protected static ?string $navigationLabel = 'Active Agents';

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table)
            ->modifyQueryUsing(function (Builder $query) {

                $period = request('period');
                $from = request('from');
                $to = request('to');

                $query->where('id', '!=', auth()->id());

                // Dashboard filter nahi hai -> sab agents dikhao
                if (
                    ($period === null || $period === 'all')
                    && blank($from)
                    && blank($to)
                ) {
                    return;
                }

                $query->whereHas('customers', function (Builder $query) use ($period, $from, $to) {

                    if ($from || $to) {
                        $query
                            ->when($from, fn ($q) => $q->whereDate('booking_date', '>=', $from))
                            ->when($to, fn ($q) => $q->whereDate('booking_date', '<=', $to));

                        return;
                    }

                    switch ($period) {

                        case 'today':
                            $query->whereDate('booking_date', today());
                            break;

                        case 'yesterday':
                            $query->whereDate('booking_date', today()->subDay());
                            break;

                        case 'this_week':
                            $query->whereBetween('booking_date', [
                                now()->startOfWeek(),
                                now()->endOfWeek(),
                            ]);
                            break;

                        case 'last_7_days':
                            $query->whereBetween('booking_date', [
                                today()->subDays(6),
                                today(),
                            ]);
                            break;

                        case 'this_month':
                            $query->whereMonth('booking_date', now()->month)
                                ->whereYear('booking_date', now()->year);
                            break;

                        case 'last_month':
                            $date = now()->subMonth();

                            $query->whereMonth('booking_date', $date->month)
                                ->whereYear('booking_date', $date->year);
                            break;

                        case 'this_quarter':
                            $query->whereBetween('booking_date', [
                                now()->startOfQuarter(),
                                now()->endOfQuarter(),
                            ]);
                            break;

                        case 'this_year':
                            $query->whereYear('booking_date', now()->year);
                            break;
                    }
                });

            });
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->role('Agent')
            ->where('id', '!=', auth()->id());
    }
}
