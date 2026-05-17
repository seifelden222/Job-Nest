<?php

namespace App\Filament\Resources\PersonProfiles\Pages;

use App\Filament\Resources\PersonProfiles\PersonProfileResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPersonProfile extends ViewRecord
{
    protected static string $resource = PersonProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
