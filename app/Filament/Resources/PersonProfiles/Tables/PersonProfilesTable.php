<?php

namespace App\Filament\Resources\PersonProfiles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PersonProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('university')
                    ->searchable(),
                TextColumn::make('major')
                    ->searchable(),
                TextColumn::make('employment_status')
                    ->searchable(),
                TextColumn::make('employment_type')
                    ->searchable(),
                TextColumn::make('current_job_title')
                    ->searchable(),
                TextColumn::make('company_name')
                    ->searchable(),
                TextColumn::make('linkedin_url')
                    ->searchable(),
                TextColumn::make('portfolio_url')
                    ->searchable(),
                TextColumn::make('preferred_work_location')
                    ->badge(),
                TextColumn::make('expected_salary_min')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('expected_salary_max')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('onboarding_step')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_profile_completed')
                    ->boolean(),
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
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
