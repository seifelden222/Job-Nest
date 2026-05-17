<?php

namespace App\Filament\Resources;

use App\Models\Admin;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

abstract class BaseResource extends Resource
{
    public static function getAuthorizationResponse(string|UnitEnum $action, ?Model $record = null): Response
    {
        $user = Filament::auth()->user();

        if ($user instanceof Admin && $user->isActive()) {
            return Response::allow();
        }

        return parent::getAuthorizationResponse($action, $record);
    }
}
