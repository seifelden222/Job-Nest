<?php

namespace App\Filament\Resources\SavedItems;

use App\Enums\SavedItemType;
use App\Filament\Resources\BaseResource;
use App\Filament\Resources\SavedItems\Pages\ManageSavedItems;
use App\Models\SavedItem;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SavedItemResource extends BaseResource
{
    protected static ?string $model = SavedItem::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-bookmark';

    protected static ?string $recordTitleAttribute = 'target_id';

    protected static ?string $navigationLabel = 'Saved Items';

    protected static string|\UnitEnum|null $navigationGroup = 'Users & Profiles';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('type')
                    ->options(SavedItemType::class)
                    ->required(),
                TextInput::make('target_id')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->label('User'),
                TextEntry::make('type')
                    ->badge(),
                TextEntry::make('target_id')
                    ->numeric(),
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
            ->recordTitleAttribute('SavedItem')
            ->columns([
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('type')
                    ->badge(),
                TextColumn::make('target_id')
                    ->numeric()
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
            'index' => ManageSavedItems::route('/'),
        ];
    }
}
