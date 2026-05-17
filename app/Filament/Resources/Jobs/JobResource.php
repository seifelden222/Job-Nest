<?php

namespace App\Filament\Resources\Jobs;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\Jobs\Pages\CreateJob;
use App\Filament\Resources\Jobs\Pages\EditJob;
use App\Filament\Resources\Jobs\Pages\ListJobs;
use App\Filament\Resources\Jobs\Pages\ViewJob;
use App\Filament\Resources\Jobs\Schemas\JobForm;
use App\Filament\Resources\Jobs\Schemas\JobInfolist;
use App\Filament\Resources\Jobs\Tables\JobsTable;
use App\Models\Job;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class JobResource extends BaseResource
{
    protected static ?string $model = Job::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationLabel = 'Jobs';

    protected static string|\UnitEnum|null $navigationGroup = 'Recruitment';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return JobForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return JobInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JobsTable::configure($table);
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
            'index' => ListJobs::route('/'),
            'create' => CreateJob::route('/create'),
            'view' => ViewJob::route('/{record}'),
            'edit' => EditJob::route('/{record}/edit'),
        ];
    }
}
