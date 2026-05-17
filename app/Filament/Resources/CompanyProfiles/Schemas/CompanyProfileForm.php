<?php

namespace App\Filament\Resources\CompanyProfiles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CompanyProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('company_name')
                    ->required(),
                TextInput::make('website')
                    ->url(),
                TextInput::make('company_size'),
                TextInput::make('industry'),
                TextInput::make('location'),
                Textarea::make('about')
                    ->columnSpanFull(),
                TextInput::make('logo'),
                TextInput::make('onboarding_step')
                    ->required()
                    ->numeric()
                    ->default(1),
                Toggle::make('is_profile_completed')
                    ->required(),
            ]);
    }
}
