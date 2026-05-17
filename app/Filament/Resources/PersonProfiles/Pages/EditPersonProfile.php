<?php

namespace App\Filament\Resources\PersonProfiles\Pages;

use App\Filament\Resources\PersonProfiles\PersonProfileResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPersonProfile extends EditRecord
{
    protected static string $resource = PersonProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
