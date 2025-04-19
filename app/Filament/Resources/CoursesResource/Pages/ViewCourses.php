<?php

namespace App\Filament\Resources\CoursesResource\Pages;

use App\Filament\Resources\CoursesResource;
use Filament\Resources\Pages\ViewRecord;

class ViewCourses extends ViewRecord
{
    protected static string $resource = CoursesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\ViewAction::make(),
        ];
    }
}