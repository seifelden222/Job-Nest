<?php

namespace App\Filament\Resources\Applications\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('job_id')
                    ->relationship('job', 'title')
                    ->preload()
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable(['name', 'email'])
                    ->preload()
                    ->required(),
                Select::make('cv_document_id')
                    ->relationship('cvDocument', 'title')
                    ->preload(),
                Textarea::make('cover_letter')
                    ->rows(4),
                Select::make('status')
                    ->options([
                        'submitted' => 'Submitted',
                        'under_review' => 'Under review',
                        'shortlisted' => 'Shortlisted',
                        'interview_scheduled' => 'Interview scheduled',
                        'offered' => 'Offered',
                        'accepted' => 'Accepted',
                        'rejected' => 'Rejected',
                        'withdrawn' => 'Withdrawn',
                    ])
                    ->default('submitted')
                    ->required(),
                TextInput::make('match_percentage')
                    ->numeric(),
                DateTimePicker::make('applied_at'),
                DateTimePicker::make('reviewed_at'),
                DateTimePicker::make('withdrawn_at'),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
