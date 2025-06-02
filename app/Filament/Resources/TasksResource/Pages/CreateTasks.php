<?php

namespace App\Filament\Resources\TasksResource\Pages;

use App\Filament\Resources\TasksResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTasks extends CreateRecord
{
    protected static string $resource = TasksResource::class;

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->user()?->can('task.create') ?? false;
    }
}
