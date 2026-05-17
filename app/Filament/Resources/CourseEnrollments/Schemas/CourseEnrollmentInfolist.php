<?php

namespace App\Filament\Resources\CourseEnrollments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CourseEnrollmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('course.title')
                    ->label('Course'),
                TextEntry::make('user.name')
                    ->label('User'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('payment_status')
                    ->badge(),
                TextEntry::make('payment_method')
                    ->badge()
                    ->placeholder('-'),
                TextEntry::make('amount_paid')
                    ->numeric(),
                TextEntry::make('enrolled_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('completed_at')
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
