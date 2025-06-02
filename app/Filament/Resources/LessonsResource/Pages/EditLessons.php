<?php

namespace App\Filament\Resources\LessonsResource\Pages;

use App\Filament\Resources\LessonsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLessons extends EditRecord
{
    protected static string $resource = LessonsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
