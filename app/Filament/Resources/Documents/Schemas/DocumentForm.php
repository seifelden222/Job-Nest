<?php

namespace App\Filament\Resources\Documents\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('type')
                    ->options(['cv' => 'Cv', 'certificate' => 'Certificate'])
                    ->required(),
                TextInput::make('title'),
                TextInput::make('file_path')
                    ->required(),
                TextInput::make('file_name')
                    ->required(),
                TextInput::make('mime_type'),
                TextInput::make('file_size')
                    ->numeric(),
                Toggle::make('is_primary')
                    ->required(),
            ]);
    }
}
