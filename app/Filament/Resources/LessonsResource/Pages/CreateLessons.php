<?php

namespace App\Filament\Resources\LessonsResource\Pages;

use App\Filament\Resources\LessonsResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateLessons extends CreateRecord
{
    protected static string $resource = LessonsResource::class;

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->user()?->can('lesson.create') ?? false;
    }
}
