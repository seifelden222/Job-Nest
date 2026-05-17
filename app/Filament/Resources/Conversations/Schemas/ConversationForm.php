<?php

namespace App\Filament\Resources\Conversations\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class ConversationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->options([
                        'direct' => 'Direct',
                        'application' => 'Application',
                        'service' => 'Service',
                        'chatbot' => 'Chatbot',
                    ])
                    ->default('direct')
                    ->required(),
                Select::make('application_id')
                    ->relationship('application', 'id')
                    ->preload(),
                Select::make('job_id')
                    ->relationship('job', 'title')
                    ->preload(),
                Select::make('service_request_id')
                    ->relationship('serviceRequest', 'title')
                    ->preload(),
                Select::make('service_proposal_id')
                    ->relationship('serviceProposal', 'id')
                    ->preload(),
                Select::make('created_by')
                    ->relationship('creator', 'name')
                    ->searchable(['name', 'email'])
                    ->preload(),
                Select::make('last_message_id')
                    ->relationship('lastMessage', 'id')
                    ->preload(),
                DateTimePicker::make('last_message_at'),
            ]);
    }
}
