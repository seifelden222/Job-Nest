<?php

namespace App\Filament\Resources\PersonProfiles\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PersonProfileInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->label('User'),
                TextEntry::make('university')
                    ->placeholder('-'),
                TextEntry::make('major')
                    ->placeholder('-'),
                TextEntry::make('employment_status')
                    ->placeholder('-'),
                TextEntry::make('employment_type')
                    ->placeholder('-'),
                TextEntry::make('current_job_title')
                    ->placeholder('-'),
                TextEntry::make('company_name')
                    ->placeholder('-'),
                TextEntry::make('linkedin_url')
                    ->placeholder('-'),
                TextEntry::make('portfolio_url')
                    ->placeholder('-'),
                TextEntry::make('preferred_work_location')
                    ->badge()
                    ->placeholder('-'),
                TextEntry::make('expected_salary_min')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('expected_salary_max')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('about')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('onboarding_step')
                    ->numeric(),
                IconEntry::make('is_profile_completed')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
