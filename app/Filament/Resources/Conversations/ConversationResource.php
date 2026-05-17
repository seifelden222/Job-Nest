<?php

namespace App\Filament\Resources\Conversations;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\Conversations\Pages\CreateConversation;
use App\Filament\Resources\Conversations\Pages\EditConversation;
use App\Filament\Resources\Conversations\Pages\ListConversations;
use App\Filament\Resources\Conversations\Pages\ViewConversation;
use App\Filament\Resources\Conversations\Schemas\ConversationForm;
use App\Filament\Resources\Conversations\Schemas\ConversationInfolist;
use App\Filament\Resources\Conversations\Tables\ConversationsTable;
use App\Models\Conversation;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ConversationResource extends BaseResource
{
    protected static ?string $model = Conversation::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $recordTitleAttribute = 'type';

    protected static ?string $navigationLabel = 'Conversations';

    protected static string|\UnitEnum|null $navigationGroup = 'AI & Chatbot';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return ConversationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ConversationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ConversationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListConversations::route('/'),
            'create' => CreateConversation::route('/create'),
            'view' => ViewConversation::route('/{record}'),
            'edit' => EditConversation::route('/{record}/edit'),
        ];
    }
}
