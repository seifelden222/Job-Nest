<?php

namespace App\Filament\Resources\ServiceProposals;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\ServiceProposals\Pages\ManageServiceProposals;
use App\Models\ServiceProposal;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServiceProposalResource extends BaseResource
{
    protected static ?string $model = ServiceProposal::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $recordTitleAttribute = 'status';

    protected static ?string $navigationLabel = 'Service Proposals';

    protected static string|\UnitEnum|null $navigationGroup = 'Services';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('service_request_id')
                    ->relationship('serviceRequest', 'title')
                    ->preload()
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable(['name', 'email'])
                    ->preload()
                    ->required(),
                Textarea::make('message')
                    ->rows(4),
                TextInput::make('proposed_budget')
                    ->numeric(),
                TextInput::make('delivery_days')
                    ->numeric(),
                Select::make('status')
                    ->options([
                        'submitted' => 'Submitted',
                        'accepted' => 'Accepted',
                        'rejected' => 'Rejected',
                        'withdrawn' => 'Withdrawn',
                    ])
                    ->default('submitted')
                    ->required(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('serviceRequest.title')
                    ->label('Service request'),
                TextEntry::make('user.name')
                    ->label('User'),
                TextEntry::make('message')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('proposed_budget')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('delivery_days')
                    ->numeric()
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
            ->recordTitleAttribute('ServiceProposal')
            ->columns([
                TextColumn::make('serviceRequest.title')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('message')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('proposed_budget')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('delivery_days')
                    ->numeric()
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
            'index' => ManageServiceProposals::route('/'),
        ];
    }
}
