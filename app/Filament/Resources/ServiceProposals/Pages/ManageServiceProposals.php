<?php

namespace App\Filament\Resources\ServiceProposals\Pages;

use App\Filament\Resources\ServiceProposals\ServiceProposalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageServiceProposals extends ManageRecords
{
    protected static string $resource = ServiceProposalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
