<?php

namespace App\Filament\Resources;

use App\Models\Task;
use App\Models\Lesson;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\TasksResource\Pages;

class TasksResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Education';
    protected static ?int $navigationSort = 8;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('task.view') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('lesson_id')
                ->label('Lesson')
                ->options(Lesson::all()->pluck('title', 'id'))
                ->searchable()
                ->required()
                ->disabled(fn() => !auth()->user()?->can('task.edit')),

            TextInput::make('title')
                ->label('Task Title')
                ->required()
                ->maxLength(255),

            RichEditor::make('instructions')
                ->label('Instructions')
                ->disableToolbarButtons(['attachFiles'])
                ->maxLength(65535)
                ->disabled(fn() => !auth()->user()?->can('task.edit')),

            TextInput::make('duration')
                ->label('Duration (minutes)')
                ->numeric()
                ->default(60)
                ->required()
                ->disabled(fn() => !auth()->user()?->can('task.edit')),

            Repeater::make('questions')
                ->relationship('questions')
                ->label('Questions')
                ->schema([
                    TextInput::make('question_text')
                        ->label('Question Text')
                        ->required(),

                    TextInput::make('score')
                        ->label('Score')
                        ->numeric()
                        ->default(1)
                        ->required(),

                    Repeater::make('options')
                        ->relationship('options')
                        ->label('Options')
                        ->schema([
                            TextInput::make('option_text')
                                ->label('Option Text')
                                ->required(),

                            Toggle::make('is_correct')
                                ->label('Is Correct?')
                                ->default(false),
                        ])
                        ->minItems(2)
                        ->createItemButtonLabel('Add Option')
                        ->afterStateUpdated(function ($state, $set) {
                            $correctCount = collect($state)->filter(fn($option) => $option['is_correct'] ?? false)->count();
                            $set('question_type', $correctCount > 1 ? 'multiple_choice' : 'single_choice');
                        }),
                ])
                ->minItems(1)
                ->createItemButtonLabel('Add Question')
                ->disabled(fn() => !auth()->user()?->can('task.edit')),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('lesson.title')
                    ->label('Lesson')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('title')
                    ->label('Title')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('duration')
                    ->label('Duration (min)')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make()->visible(fn() => auth()->user()?->can('task.edit')),
                Tables\Actions\DeleteAction::make()->visible(fn() => auth()->user()?->can('task.delete')),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTasks::route('/create'),
            'edit' => Pages\EditTasks::route('/{record}/edit'),
        ];
    }
}