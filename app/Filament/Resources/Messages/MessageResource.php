<?php

namespace App\Filament\Resources\Messages;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\Messages\Pages\ManageMessages;
use App\Models\Message;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MessageResource extends BaseResource
{
    protected static ?string $model = Message::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    protected static ?string $recordTitleAttribute = 'body';

    protected static ?string $navigationLabel = 'Messages';

    protected static string|\UnitEnum|null $navigationGroup = 'AI & Chatbot';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('conversation_id')
                    ->relationship('conversation', 'id')
                    ->preload()
                    ->required(),
                Select::make('sender_id')
                    ->relationship('sender', 'name')
                    ->searchable(['name', 'email'])
                    ->preload(),
                Select::make('message_role')
                    ->options(['user' => 'User', 'assistant' => 'Assistant', 'system' => 'System'])
                    ->default('user')
                    ->required(),
                Select::make('message_type')
                    ->options(['text' => 'Text', 'file' => 'File', 'system' => 'System'])
                    ->default('text')
                    ->required(),
                Textarea::make('body')
                    ->rows(4),
                TextInput::make('attachment_path'),
                TextInput::make('attachment_name'),
                TextInput::make('attachment_mime_type'),
                TextInput::make('attachment_size')
                    ->numeric(),
                Toggle::make('is_edited')
                    ->required(),
                DateTimePicker::make('edited_at'),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('conversation.id')
                    ->label('Conversation'),
                TextEntry::make('sender.name')
                    ->label('Sender')
                    ->placeholder('-'),
                TextEntry::make('message_role')
                    ->badge(),
                TextEntry::make('message_type')
                    ->badge(),
                TextEntry::make('body')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('attachment_path')
                    ->placeholder('-'),
                TextEntry::make('attachment_name')
                    ->placeholder('-'),
                TextEntry::make('attachment_mime_type')
                    ->placeholder('-'),
                TextEntry::make('attachment_size')
                    ->numeric()
                    ->placeholder('-'),
                IconEntry::make('is_edited')
                    ->boolean(),
                TextEntry::make('edited_at')
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

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Message')
            ->columns([
                TextColumn::make('conversation.id')
                    ->searchable(),
                TextColumn::make('sender.name')
                    ->searchable(),
                TextColumn::make('message_role')
                    ->badge(),
                TextColumn::make('message_type')
                    ->badge(),
                TextColumn::make('body')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('attachment_path')
                    ->searchable(),
                TextColumn::make('attachment_name')
                    ->searchable(),
                TextColumn::make('attachment_mime_type')
                    ->searchable(),
                TextColumn::make('attachment_size')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_edited')
                    ->boolean(),
                TextColumn::make('edited_at')
                    ->dateTime()
                    ->sortable(),
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
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMessages::route('/'),
        ];
    }
}
