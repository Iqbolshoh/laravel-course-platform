<?php

namespace App\Filament\Resources;

use App\Models\Task;
use App\Models\Lesson;
use Filament\Forms;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Resources\Resource;
use App\Filament\Resources\TasksResource\Pages;
use App\Filament\Resources\TasksResource\RelationManagers\TaskQuestionsRelationManager;
use App\Filament\Resources\TasksResource\RelationManagers\TaskResultsRelationManager;

class TasksResource extends Resource
{
    protected static ?string $model = Task::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Education';
    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('lesson_id')
                ->label('Lesson')
                ->relationship('lesson', 'title')
                ->required(),

            TextInput::make('title')
                ->label('Task Title')
                ->required()
                ->maxLength(255),

            RichEditor::make('instructions')
                ->label('Instructions')
                ->maxLength(65535),

            TextInput::make('duration')
                ->label('Duration (minutes)')
                ->numeric()
                ->default(60)
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('lesson.title')->label('Lesson')->sortable()->searchable(),
                TextColumn::make('title')->label('Title')->sortable()->searchable(),
                TextColumn::make('duration')->label('Duration (min)'),
                TextColumn::make('created_at')->dateTime()->label('Created At'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTasks::route('/create'),
            'edit' => Pages\EditTasks::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            TaskQuestionsRelationManager::class,
            TaskResultsRelationManager::class,
        ];
    }
}
