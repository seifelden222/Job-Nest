<?php

namespace App\Filament\Resources\Courses\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CourseInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title')
                    ->label('Course title'),
                TextEntry::make('owner.name')
                    ->label('Instructor / Owner'),
                TextEntry::make('category.name')
                    ->label('Category')
                    ->placeholder('-'),
                TextEntry::make('slug'),
                TextEntry::make('url')
                    ->placeholder('-'),
                TextEntry::make('thumbnail')
                    ->placeholder('-'),
                TextEntry::make('short_description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('level')
                    ->badge(),
                TextEntry::make('delivery_mode')
                    ->badge(),
                TextEntry::make('language'),
                TextEntry::make('price')
                    ->money(),
                TextEntry::make('currency'),
                TextEntry::make('duration_hours')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('seats_count')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('start_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('end_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->badge(),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('ai_course_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('course_overview')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('what_you_learn')
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
}
