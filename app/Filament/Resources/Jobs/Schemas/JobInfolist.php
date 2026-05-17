<?php

namespace App\Filament\Resources\Jobs\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class JobInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title')
                    ->label('Job title'),
                TextEntry::make('company.name')
                    ->label('Company'),
                TextEntry::make('category.name')
                    ->label('Category')
                    ->placeholder('-'),
                TextEntry::make('industry')
                    ->placeholder('-'),
                TextEntry::make('location')
                    ->placeholder('-'),
                TextEntry::make('employment_type')
                    ->placeholder('-'),
                TextEntry::make('salary_min')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('salary_max')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('currency')
                    ->placeholder('-'),
                TextEntry::make('experience_level')
                    ->placeholder('-'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('requirements')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('responsibilities')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('deadline')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->badge(),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('applications_count')
                    ->numeric(),
                TextEntry::make('ai_job_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
