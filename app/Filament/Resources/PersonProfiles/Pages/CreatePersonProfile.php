<?php

namespace App\Filament\Resources\PersonProfiles\Pages;

use App\Filament\Resources\PersonProfiles\PersonProfileResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePersonProfile extends CreateRecord
{
    protected static string $resource = PersonProfileResource::class;
}
