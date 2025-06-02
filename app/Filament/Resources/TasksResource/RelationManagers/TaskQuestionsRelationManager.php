<?php

namespace App\Filament\Resources\TasksResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;

class TaskQuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';
    protected static ?string $title = 'Task Questions';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                TextInput::make('question_text')->required(),
                Select::make('question_type')
                    ->options([
                        'single' => 'Single Choice',
                        'multiple' => 'Multiple Choice',
                        'text' => 'Text Answer',
                    ])
                    ->required(),

                TextInput::make('score')->numeric()->default(1),

                Repeater::make('options')
                    ->relationship('options')
                    ->schema([
                        TextInput::make('option_text')->required(),
                        Toggle::make('is_correct')->label('Correct Answer')->default(false),
                    ])
                    ->defaultItems(2)
                    ->minItems(2)
                    ->label('Answer Options'),
            ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            TextColumn::make('question_text')->label('Question'),
            TextColumn::make('question_type')->label('Type'),
            TextColumn::make('score')->label('Score'),
        ]);
    }
}
