<?php

namespace App\Filament\Resources\Applications;

use App\Filament\Resources\Applications\Pages\CreateApplication;
use App\Filament\Resources\Applications\Pages\EditApplication;
use App\Filament\Resources\Applications\Pages\ListApplications;
use App\Filament\Resources\Applications\Pages\ViewApplication;
use App\Filament\Resources\Applications\Schemas\ApplicationForm;
use App\Filament\Resources\Applications\Schemas\ApplicationInfolist;
use App\Filament\Resources\Applications\Tables\ApplicationsTable;
use App\Filament\Resources\BaseResource;
use App\Models\Application;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ApplicationResource extends BaseResource
{
    protected static ?string $model = Application::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $recordTitleAttribute = 'status';

    protected static ?string $navigationLabel = 'Applications';

    protected static string|\UnitEnum|null $navigationGroup = 'Recruitment';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return ApplicationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ApplicationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ApplicationsTable::configure($table);
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
            'index' => ListApplications::route('/'),
            'create' => CreateApplication::route('/create'),
            'view' => ViewApplication::route('/{record}'),
            'edit' => EditApplication::route('/{record}/edit'),
        ];
    }
}
