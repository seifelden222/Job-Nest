<?php

namespace App\Filament\Resources\SavedItems\Pages;

use App\Filament\Resources\SavedItems\SavedItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSavedItems extends ManageRecords
{
    protected static string $resource = SavedItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
