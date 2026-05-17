<?php

namespace App\Filament\Resources\RefreshTokens;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\RefreshTokens\Pages\ManageRefreshTokens;
use App\Models\RefreshToken;
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
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RefreshTokenResource extends BaseResource
{
    protected static ?string $model = RefreshToken::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-finger-print';

    protected static ?string $recordTitleAttribute = 'family_id';

    protected static ?string $navigationLabel = 'Refresh Tokens';

    protected static string|\UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('access_token_id')
                    ->relationship('accessToken', 'name'),
                TextInput::make('family_id')
                    ->required(),
                Select::make('replaced_by_token_id')
                    ->relationship('replacedByToken', 'name'),
                TextInput::make('name'),
                TextInput::make('token_hash')
                    ->required(),
                TextInput::make('ip_address'),
                Textarea::make('user_agent')
                    ->columnSpanFull(),
                DateTimePicker::make('last_used_at'),
                DateTimePicker::make('revoked_at'),
                DateTimePicker::make('expires_at')
                    ->required(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->label('User'),
                TextEntry::make('accessToken.name')
                    ->label('Access token')
                    ->placeholder('-'),
                TextEntry::make('family_id'),
                TextEntry::make('replacedByToken.name')
                    ->label('Replaced by token')
                    ->placeholder('-'),
                TextEntry::make('name')
                    ->placeholder('-'),
                TextEntry::make('token_hash'),
                TextEntry::make('ip_address')
                    ->placeholder('-'),
                TextEntry::make('user_agent')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('last_used_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('revoked_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('expires_at')
                    ->dateTime(),
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
            ->recordTitleAttribute('RefreshToken')
            ->columns([
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('accessToken.name')
                    ->searchable(),
                TextColumn::make('family_id')
                    ->searchable(),
                TextColumn::make('replacedByToken.name')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('token_hash')
                    ->searchable(),
                TextColumn::make('ip_address')
                    ->searchable(),
                TextColumn::make('last_used_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('revoked_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('expires_at')
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
            'index' => ManageRefreshTokens::route('/'),
        ];
    }
}
