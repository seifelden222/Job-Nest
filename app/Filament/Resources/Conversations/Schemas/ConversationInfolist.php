<?php

namespace App\Filament\Resources\Conversations\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ConversationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('type')
                    ->badge(),
                TextEntry::make('application.id')
                    ->label('Application')
                    ->placeholder('-'),
                TextEntry::make('job.title')
                    ->label('Job')
                    ->placeholder('-'),
                TextEntry::make('serviceRequest.title')
                    ->label('Service request')
                    ->placeholder('-'),
                TextEntry::make('serviceProposal.id')
                    ->label('Service proposal')
                    ->placeholder('-'),
                TextEntry::make('creator.name')
                    ->label('Created by')
                    ->placeholder('-'),
                TextEntry::make('lastMessage.id')
                    ->label('Last message')
                    ->placeholder('-'),
                TextEntry::make('last_message_at')
                    ->dateTime()
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
