<?php

namespace App\Filament\Agent\Resources\VisitingOrders;

use App\Filament\Agent\Resources\VisitingOrders\Pages\CreateVisitingOrder;
use App\Filament\Agent\Resources\VisitingOrders\Pages\EditVisitingOrder;
use App\Filament\Agent\Resources\VisitingOrders\Pages\ListVisitingOrders;
use App\Filament\Agent\Resources\VisitingOrders\Schemas\VisitingOrderForm;
use App\Filament\Agent\Resources\VisitingOrders\Tables\VisitingOrdersTable;
use App\Models\VisitingOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VisitingOrderResource extends Resource
{
    protected static ?string $model = VisitingOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $recordTitleAttribute = 'visitor_name';

    public static function form(Schema $schema): Schema
    {
        return VisitingOrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VisitingOrdersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->check() && auth()->user()->hasRole('Agent')) {
            $query->where('agent_id', auth()->id());
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVisitingOrders::route('/'),
            'create' => CreateVisitingOrder::route('/create'),
            'edit' => EditVisitingOrder::route('/{record}/edit'),
        ];
    }
}
