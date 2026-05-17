<?php

namespace App\Filament\Resources\CourseReviews\Pages;

use App\Filament\Resources\CourseReviews\CourseReviewResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCourseReviews extends ManageRecords
{
    protected static string $resource = CourseReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
