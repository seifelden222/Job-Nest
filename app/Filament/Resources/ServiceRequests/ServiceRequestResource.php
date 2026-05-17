<?php

namespace App\Filament\Resources\ServiceRequests;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\ServiceRequests\Pages\ManageServiceRequests;
use App\Models\ServiceRequest;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServiceRequestResource extends BaseResource
{
    protected static ?string $model = ServiceRequest::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationLabel = 'Service Requests';

    protected static string|\UnitEnum|null $navigationGroup = 'Services';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('owner', 'name')
                    ->label('Requested by')
                    ->searchable(['name', 'email'])
                    ->preload()
                    ->required(),
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->preload(),
                TextInput::make('title')
                    ->label('Request title')
                    ->required(),
                Textarea::make('description')
                    ->required(),
                TextInput::make('budget_min')
                    ->numeric(),
                TextInput::make('budget_max')
                    ->numeric(),
                TextInput::make('currency'),
                TextInput::make('location'),
                Select::make('delivery_mode')
                    ->options(['online' => 'Online', 'offline' => 'Offline', 'hybrid' => 'Hybrid']),
                DatePicker::make('deadline'),
                Select::make('status')
                    ->options([
                        'open' => 'Open',
                        'in_progress' => 'In progress',
                        'closed' => 'Closed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('open')
                    ->required(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title')
                    ->label('Request title'),
                TextEntry::make('owner.name')
                    ->label('Requested by'),
                TextEntry::make('category.name')
                    ->label('Category')
                    ->placeholder('-'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('budget_min')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('budget_max')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('currency')
                    ->placeholder('-'),
                TextEntry::make('location')
                    ->placeholder('-'),
                TextEntry::make('delivery_mode')
                    ->badge()
                    ->placeholder('-'),
                TextEntry::make('deadline')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->badge(),
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
            ->recordTitleAttribute('ServiceRequest')
            ->columns([
                TextColumn::make('title')
                    ->label('Request title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('owner.name')
                    ->label('Requested by')
                    ->searchable(),
                TextColumn::make('category.name')
                    ->searchable(),
                TextColumn::make('description')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('budget_min')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('budget_max')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('currency')
                    ->searchable(),
                TextColumn::make('location')
                    ->searchable(),
                TextColumn::make('delivery_mode')
                    ->badge(),
                TextColumn::make('deadline')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
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
            'index' => ManageServiceRequests::route('/'),
        ];
    }
}
