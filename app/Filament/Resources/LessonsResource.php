<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LessonsResource\Pages;
use App\Filament\Resources\LessonsResource\RelationManagers;
use App\Models\Lesson;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LessonsResource extends Resource
{
    protected static ?string $model = Lesson::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'Education';
    protected static ?int $navigationSort = 7;

    /*
    |-------------------------------------------------------------------------- 
    | Access Control Check
    |-------------------------------------------------------------------------- 
    */
    public static function canAccess(string $permission = 'view'): bool
    {
        if (!$user = auth()->user())
            return false;

        return match ($permission) {
            'view' => $user->can('lesson.view'),
            'create' => $user->can('lesson.create'),
            'edit' => $user->can('lesson.edit'),
            'delete' => $user->can('lesson.delete'),
            default => false,
        };
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('course_id')
                    ->label('Course')
                    ->relationship('course', 'title')
                    ->required(),

                TextInput::make('title')
                    ->label('Lesson Title')
                    ->required()
                    ->maxLength(255),

                TextInput::make('video_url')
                    ->label('YouTube Video URL')
                    ->url()
                    ->placeholder('https://www.youtube.com/watch?v=XXXX')
                    ->helperText('Faqat YouTube videosining asosiy URL qismini kiriting.')
                    ->dehydrateStateUsing(function ($state) {
                        parse_str(parse_url($state, PHP_URL_QUERY), $query);
                        return isset($query['v']) ? 'https://www.youtube.com/watch?v=' . $query['v'] : $state;
                    }),

                RichEditor::make('content')
                    ->label('Lesson Content')
                    ->maxLength(65535),

                TextInput::make('order')
                    ->label('Order')
                    ->numeric()
                    ->default(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('course.title')
                    ->label('Course')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('title')
                    ->label('Title')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('order')
                    ->label('Order')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Created At'),
            ])
            ->filters([])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
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
