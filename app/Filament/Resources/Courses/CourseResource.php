<?php

namespace App\Filament\Resources\Courses;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\Courses\Pages\CreateCourse;
use App\Filament\Resources\Courses\Pages\EditCourse;
use App\Filament\Resources\Courses\Pages\ListCourses;
use App\Filament\Resources\Courses\Pages\ViewCourse;
use App\Filament\Resources\Courses\Schemas\CourseForm;
use App\Filament\Resources\Courses\Schemas\CourseInfolist;
use App\Filament\Resources\Courses\Tables\CoursesTable;
use App\Models\Course;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CourseResource extends BaseResource
{
    protected static ?string $model = Course::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationLabel = 'Courses';

    protected static string|\UnitEnum|null $navigationGroup = 'Learning';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return CourseForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CourseInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CoursesTable::configure($table);
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
            'index' => ListCourses::route('/'),
            'create' => CreateCourse::route('/create'),
            'view' => ViewCourse::route('/{record}'),
            'edit' => EditCourse::route('/{record}/edit'),
        ];
    }
}
