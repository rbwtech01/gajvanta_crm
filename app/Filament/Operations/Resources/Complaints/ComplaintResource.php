<?php

namespace App\Filament\Operations\Resources\Complaints;

use App\Filament\Operations\Resources\Complaints\Pages\CreateComplaint;
use App\Filament\Operations\Resources\Complaints\Pages\EditComplaint;
use App\Filament\Operations\Resources\Complaints\Pages\ListComplaints;
use App\Filament\Operations\Resources\Complaints\Schemas\ComplaintForm;
use App\Filament\Operations\Resources\Complaints\Tables\ComplaintsTable;
use App\Models\Complaint;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ComplaintResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $recordTitleAttribute = 'complaint';

    protected static ?int $navigationSort = 2;


    public static function form(Schema $schema): Schema
    {
        return ComplaintForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ComplaintsTable::configure($table);
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
            'index' => ListComplaints::route('/'),
            'create' => CreateComplaint::route('/create'),
            'edit' => EditComplaint::route('/{record}/edit'),
        ];
    }
}
