<?php

namespace App\Filament\Shipment\Resources\Customers;

use App\Filament\Shipment\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Shipment\Resources\Customers\Pages\EditCustomer;
use App\Filament\Shipment\Resources\Customers\Pages\ListCustomers;
use App\Filament\Shipment\Resources\Customers\Schemas\CustomerForm;
use App\Filament\Shipment\Resources\Customers\Tables\CustomersTable;
use App\Models\Customer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Truck;

    protected static ?string $navigationLabel = 'Shipments';

    protected static ?string $recordTitleAttribute = 'customer_name';

    public static function form(Schema $schema): Schema
    {
        return CustomerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomersTable::configure($table)
            ->modifyQueryUsing(function (Builder $query): Builder {
                return $query->when(
                    request('status'),
                    fn (Builder $query, string $status) => $query->where('status', $status),
                    fn (Builder $query) => $query->where('status', 'packed')
                );
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

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
