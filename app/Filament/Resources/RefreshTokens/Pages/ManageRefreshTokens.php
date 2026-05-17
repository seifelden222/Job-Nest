<?php

namespace App\Filament\Resources\RefreshTokens\Pages;

use App\Filament\Resources\RefreshTokens\RefreshTokenResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageRefreshTokens extends ManageRecords
{
    protected static string $resource = RefreshTokenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
