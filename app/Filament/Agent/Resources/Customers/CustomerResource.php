<?php

namespace App\Filament\Agent\Resources\Customers;

use App\Filament\Agent\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Agent\Resources\Customers\Pages\EditCustomer;
use App\Filament\Agent\Resources\Customers\Pages\ListCustomers;
use App\Filament\Agent\Resources\Customers\Schemas\CustomerForm;
use App\Filament\Agent\Resources\Customers\Tables\CustomersTable;
use App\Models\Customer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $recordTitleAttribute = 'customer_name';

    public static function form(Schema $schema): Schema
    {
        return CustomerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomersTable::configure($table)
            ->modifyQueryUsing(function (Builder $query): Builder {
                $period = request('period');
                $status = request('status');

                return $query
                    ->when($status, fn (Builder $query) => $query->where('status', $status))
                    ->when($period === 'today', fn (Builder $query) => $query->whereDate('booking_date', today()))
                    ->when($period === 'yesterday', fn (Builder $query) => $query->whereDate('booking_date', today()->subDay()))
                    ->when($period === 'this_week', fn (Builder $query) => $query->whereBetween('booking_date', [now()->startOfWeek(), now()->endOfWeek()]))
                    ->when($period === 'this_month', fn (Builder $query) => $query->whereMonth('booking_date', now()->month)->whereYear('booking_date', now()->year))
                    ->when($period === 'this_year', fn (Builder $query) => $query->whereYear('booking_date', now()->year));
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
            'index' => ListCustomers::route('/'),
            'create' => CreateCustomer::route('/create'),
            'edit' => EditCustomer::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id());
    }
}
