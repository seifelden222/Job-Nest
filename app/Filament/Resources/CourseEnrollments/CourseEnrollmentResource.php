<?php

namespace App\Filament\Resources\CourseEnrollments;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\CourseEnrollments\Pages\CreateCourseEnrollment;
use App\Filament\Resources\CourseEnrollments\Pages\EditCourseEnrollment;
use App\Filament\Resources\CourseEnrollments\Pages\ListCourseEnrollments;
use App\Filament\Resources\CourseEnrollments\Pages\ViewCourseEnrollment;
use App\Filament\Resources\CourseEnrollments\Schemas\CourseEnrollmentForm;
use App\Filament\Resources\CourseEnrollments\Schemas\CourseEnrollmentInfolist;
use App\Filament\Resources\CourseEnrollments\Tables\CourseEnrollmentsTable;
use App\Models\CourseEnrollment;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CourseEnrollmentResource extends BaseResource
{
    protected static ?string $model = CourseEnrollment::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $recordTitleAttribute = 'status';

    protected static ?string $navigationLabel = 'Enrollments';

    protected static string|\UnitEnum|null $navigationGroup = 'Learning';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return CourseEnrollmentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CourseEnrollmentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CourseEnrollmentsTable::configure($table);
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
            'index' => ListCourseEnrollments::route('/'),
            'create' => CreateCourseEnrollment::route('/create'),
            'view' => ViewCourseEnrollment::route('/{record}'),
            'edit' => EditCourseEnrollment::route('/{record}/edit'),
        ];
    }
}
