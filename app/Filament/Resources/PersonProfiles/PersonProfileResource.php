<?php

namespace App\Filament\Resources\PersonProfiles;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\PersonProfiles\Pages\CreatePersonProfile;
use App\Filament\Resources\PersonProfiles\Pages\EditPersonProfile;
use App\Filament\Resources\PersonProfiles\Pages\ListPersonProfiles;
use App\Filament\Resources\PersonProfiles\Pages\ViewPersonProfile;
use App\Filament\Resources\PersonProfiles\Schemas\PersonProfileForm;
use App\Filament\Resources\PersonProfiles\Schemas\PersonProfileInfolist;
use App\Filament\Resources\PersonProfiles\Tables\PersonProfilesTable;
use App\Models\PersonProfile;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PersonProfileResource extends BaseResource
{
    protected static ?string $model = PersonProfile::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-identification';

    protected static ?string $recordTitleAttribute = 'current_job_title';

    protected static ?string $navigationLabel = 'Person Profiles';

    protected static string|\UnitEnum|null $navigationGroup = 'Users & Profiles';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return PersonProfileForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PersonProfileInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PersonProfilesTable::configure($table);
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
            'index' => ListPersonProfiles::route('/'),
            'create' => CreatePersonProfile::route('/create'),
            'view' => ViewPersonProfile::route('/{record}'),
            'edit' => EditPersonProfile::route('/{record}/edit'),
        ];
    }
}
