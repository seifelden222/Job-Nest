<?php

namespace App\Filament\Resources\CourseReviews;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\CourseReviews\Pages\ManageCourseReviews;
use App\Models\CourseReview;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CourseReviewResource extends BaseResource
{
    protected static ?string $model = CourseReview::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-star';

    protected static ?string $recordTitleAttribute = 'rating';

    protected static ?string $navigationLabel = 'Reviews';

    protected static string|\UnitEnum|null $navigationGroup = 'Learning';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('course_id')
                    ->relationship('course', 'title')
                    ->preload()
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable(['name', 'email'])
                    ->preload()
                    ->required(),
                TextInput::make('rating')
                    ->required()
                    ->numeric(),
                Textarea::make('comment')
                    ->rows(3),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('course.title')
                    ->label('Course'),
                TextEntry::make('user.name')
                    ->label('User'),
                TextEntry::make('rating')
                    ->numeric(),
                TextEntry::make('comment')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('CourseReview')
            ->columns([
                TextColumn::make('course.title')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('rating')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('comment')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCourseReviews::route('/'),
        ];
    }
}
