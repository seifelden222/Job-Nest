<?php

namespace App\Filament\Resources\PersonProfiles\Pages;

use App\Filament\Resources\PersonProfiles\PersonProfileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPersonProfiles extends ListRecords
{
    protected static string $resource = PersonProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
