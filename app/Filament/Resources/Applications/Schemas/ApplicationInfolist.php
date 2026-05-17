<?php

namespace App\Filament\Resources\Applications\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ApplicationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('job.title')
                    ->label('Job'),
                TextEntry::make('user.name')
                    ->label('User'),
                TextEntry::make('cvDocument.title')
                    ->label('Cv document')
                    ->placeholder('-'),
                TextEntry::make('cover_letter')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('match_percentage')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('applied_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('reviewed_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('withdrawn_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('notes')
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
