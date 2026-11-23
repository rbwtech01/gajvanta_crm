<?php

namespace App\Filament\Resources\MachineModelAttachments;

use App\Filament\Resources\MachineModelAttachments\Pages\CreateMachineModelAttachment;
use App\Filament\Resources\MachineModelAttachments\Pages\EditMachineModelAttachment;
use App\Filament\Resources\MachineModelAttachments\Pages\ListMachineModelAttachments;
use App\Filament\Resources\MachineModelAttachments\Schemas\MachineModelAttachmentForm;
use App\Filament\Resources\MachineModelAttachments\Tables\MachineModelAttachmentsTable;
use App\Models\MachineModelAttachment;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MachineModelAttachmentResource extends Resource
{
    protected static ?string $model = MachineModelAttachment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperClip;

    protected static ?string $recordTitleAttribute = 'title';

    protected static bool $shouldRegisterNavigation = false;

    protected static string|UnitEnum|null $navigationGroup = 'Product Management';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return MachineModelAttachmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MachineModelAttachmentsTable::configure($table);
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
            'index' => ListMachineModelAttachments::route('/'),
            'create' => CreateMachineModelAttachment::route('/create'),
            'edit' => EditMachineModelAttachment::route('/{record}/edit'),
        ];
    }
}
