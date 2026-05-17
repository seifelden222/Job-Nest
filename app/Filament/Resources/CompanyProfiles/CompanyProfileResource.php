<?php

namespace App\Filament\Resources\CompanyProfiles;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\CompanyProfiles\Pages\CreateCompanyProfile;
use App\Filament\Resources\CompanyProfiles\Pages\EditCompanyProfile;
use App\Filament\Resources\CompanyProfiles\Pages\ListCompanyProfiles;
use App\Filament\Resources\CompanyProfiles\Pages\ViewCompanyProfile;
use App\Filament\Resources\CompanyProfiles\Schemas\CompanyProfileForm;
use App\Filament\Resources\CompanyProfiles\Schemas\CompanyProfileInfolist;
use App\Filament\Resources\CompanyProfiles\Tables\CompanyProfilesTable;
use App\Models\CompanyProfile;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CompanyProfileResource extends BaseResource
{
    protected static ?string $model = CompanyProfile::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $recordTitleAttribute = 'company_name';

    protected static ?string $navigationLabel = 'Company Profiles';

    protected static string|\UnitEnum|null $navigationGroup = 'Users & Profiles';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return CompanyProfileForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CompanyProfileInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CompanyProfilesTable::configure($table);
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
            'index' => ListCompanyProfiles::route('/'),
            'create' => CreateCompanyProfile::route('/create'),
            'view' => ViewCompanyProfile::route('/{record}'),
            'edit' => EditCompanyProfile::route('/{record}/edit'),
        ];
    }
}
