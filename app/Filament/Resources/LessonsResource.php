<?php

namespace App\Filament\Resources;

use App\Models\Lesson;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;

use App\Filament\Resources\LessonsResource\Pages;

class LessonsResource extends Resource
{
    protected static ?string $model = Lesson::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'Education';
    protected static ?int $navigationSort = 7;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('lesson.view') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('course_id')
                ->label('Course')
                ->relationship('course', 'title')
                ->required()
                ->disabled(fn() => !auth()->user()?->can('lesson.edit')),

            TextInput::make('title')
                ->label('Lesson Title')
                ->required()
                ->maxLength(255)
                ->disabled(fn() => !auth()->user()?->can('lesson.edit')),

            TextInput::make('video_url')
                ->label('YouTube Video URL')
                ->url()
                ->placeholder('https://www.youtube.com/watch?v=XXXX')
                ->helperText('Just enter the main URL part of the YouTube video.')
                ->dehydrateStateUsing(function ($state) {
                    parse_str(parse_url($state, PHP_URL_QUERY), $query);
                    return isset($query['v']) ? 'https://www.youtube.com/watch?v=' . $query['v'] : $state;
                })
                ->disabled(fn() => !auth()->user()?->can('lesson.edit')),

            RichEditor::make('content')
                ->label('Lesson Content')
                ->disableToolbarButtons(['attachFiles'])
                ->maxLength(65535)
                ->disabled(fn() => !auth()->user()?->can('lesson.edit')),

            TextInput::make('order')
                ->label('Order')
                ->numeric()
                ->default(1)
                ->disabled(fn() => !auth()->user()?->can('lesson.edit')),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('order')
            ->defaultSort('order')
            ->columns([
                TextColumn::make('course.title')
                    ->label('Course')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('order')
                    ->label('Order')
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Title')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Created At'),
            ])
            ->filters([])
            ->actions([
                EditAction::make()->visible(fn() => auth()->user()?->can('lesson.edit')),
                DeleteAction::make()->visible(fn() => auth()->user()?->can('lesson.delete')),
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
            'index' => Pages\ListLessons::route('/'),
            'create' => Pages\CreateLessons::route('/create'),
            'edit' => Pages\EditLessons::route('/{record}/edit'),
        ];
    }
}
