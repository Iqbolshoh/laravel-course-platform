<?php

namespace App\Filament\Resources\TasksResource\RelationManagers;

use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;

class TaskResultsRelationManager extends RelationManager
{
    protected static string $relationship = 'results';
    protected static ?string $title = 'Task Results';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            TextColumn::make('user.name')->label('User'),
            TextColumn::make('score')->label('Score'),
            TextColumn::make('completed_at')->dateTime()->label('Completed'),
        ]);
    }
}
