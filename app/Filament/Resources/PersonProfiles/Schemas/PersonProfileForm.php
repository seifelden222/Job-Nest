<?php

namespace App\Filament\Resources\PersonProfiles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PersonProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('university'),
                TextInput::make('major'),
                TextInput::make('employment_status'),
                TextInput::make('employment_type'),
                TextInput::make('current_job_title'),
                TextInput::make('company_name'),
                TextInput::make('linkedin_url')
                    ->url(),
                TextInput::make('portfolio_url')
                    ->url(),
                Select::make('preferred_work_location')
                    ->options(['onsite' => 'Onsite', 'remote' => 'Remote', 'hybrid' => 'Hybrid']),
                TextInput::make('expected_salary_min')
                    ->numeric(),
                TextInput::make('expected_salary_max')
                    ->numeric(),
                Textarea::make('about')
                    ->columnSpanFull(),
                TextInput::make('onboarding_step')
                    ->required()
                    ->numeric()
                    ->default(1),
                Toggle::make('is_profile_completed')
                    ->required(),
            ]);
    }
}
