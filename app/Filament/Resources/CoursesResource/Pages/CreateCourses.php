<?php

namespace App\Filament\Resources\CoursesResource\Pages;

use App\Filament\Resources\CoursesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCourses extends CreateRecord
{
    protected static string $resource = CoursesResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['teacher_id'] = auth()->id();
        return $data;
    }
}
