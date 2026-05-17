<?php

namespace App\Filament\Resources\Courses\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('owner', 'name')
                    ->label('Instructor / Owner')
                    ->searchable(['name', 'email'])
                    ->preload()
                    ->required(),
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->preload(),
                TextInput::make('title')
                    ->label('Course title')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('url')
                    ->url(),
                TextInput::make('thumbnail'),
                Textarea::make('short_description')
                    ->rows(2),
                Textarea::make('description')
                    ->rows(4),
                Textarea::make('course_overview')
                    ->rows(3),
                Textarea::make('what_you_learn')
                    ->rows(3),
                Select::make('level')
                    ->options(['beginner' => 'Beginner', 'intermediate' => 'Intermediate', 'advanced' => 'Advanced'])
                    ->default('beginner')
                    ->required(),
                Select::make('delivery_mode')
                    ->options(['online' => 'Online', 'offline' => 'Offline', 'hybrid' => 'Hybrid'])
                    ->default('online')
                    ->required(),
                TextInput::make('language')
                    ->required()
                    ->default('en'),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->prefix('$'),
                TextInput::make('currency')
                    ->required()
                    ->default('EGP'),
                TextInput::make('duration_hours')
                    ->numeric(),
                TextInput::make('seats_count')
                    ->numeric(),
                DatePicker::make('start_date'),
                DatePicker::make('end_date'),
                Select::make('status')
                    ->options(['draft' => 'Draft', 'published' => 'Published', 'closed' => 'Closed', 'archived' => 'Archived'])
                    ->default('draft')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
                TextInput::make('ai_course_id')
                    ->numeric(),
            ]);
    }
}
