<?php

namespace App\Filament\Resources\Jobs\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class JobForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->relationship('company', 'name')
                    ->searchable(['name', 'email'])
                    ->preload()
                    ->required(),
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->preload(),
                TextInput::make('industry'),
                TextInput::make('title')
                    ->label('Job title')
                    ->required(),
                Textarea::make('description')
                    ->required(),
                TextInput::make('location'),
                TextInput::make('employment_type'),
                TextInput::make('salary_min')
                    ->numeric(),
                TextInput::make('salary_max')
                    ->numeric(),
                TextInput::make('currency'),
                TextInput::make('experience_level'),
                Textarea::make('requirements')
                    ->rows(3),
                Textarea::make('responsibilities')
                    ->rows(3),
                DatePicker::make('deadline'),
                Select::make('status')
                    ->options(['draft' => 'Draft', 'active' => 'Active', 'closed' => 'Closed', 'archived' => 'Archived'])
                    ->default('draft')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
                TextInput::make('applications_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('ai_job_id')
                    ->numeric(),
            ]);
    }
}
