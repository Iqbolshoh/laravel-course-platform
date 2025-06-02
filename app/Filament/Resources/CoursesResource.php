<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CoursesResource\Pages;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CoursesResource extends Resource
{
    protected static ?string $model = Course::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'Education';
    protected static ?int $navigationSort = 6;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('user.view') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->label('Course Title')
                    ->required()
                    ->maxLength(255)
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('slug', Str::slug($state));
                    })
                    ->disabled(fn() => !auth()->user()?->can('course.edit')),

                Hidden::make('slug'),

                RichEditor::make('description')
                    ->label('Description')
                    ->disableToolbarButtons(['attachFiles'])
                    ->maxLength(65535)
                    ->disabled(fn() => !auth()->user()?->can('course.edit')),

                FileUpload::make('image')
                    ->label('Course Image')
                    ->required()
                    ->image()
                    ->imageEditor()
                    ->directory('courses')
                    ->imageEditorMode(2)
                    ->openable()
                    ->downloadable()
                    ->previewable()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                    ->disabled(fn() => !auth()->user()?->can('course.edit')),

                TextInput::make('price')
                    ->label('Price (UZS)')
                    ->numeric()
                    ->required()
                    ->disabled(fn() => !auth()->user()?->can('course.edit')),

                TextInput::make('discount')
                    ->label('Discount (%)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(0)
                    ->disabled(fn() => !auth()->user()?->can('course.edit')),

                Toggle::make('is_published')
                    ->label('Published')
                    ->default(false)
                    ->disabled(fn() => !auth()->user()?->can('course.edit')),

                Hidden::make('teacher_id')
                    ->default(fn() => auth()->id())
                    ->dehydrated(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->sortable()->searchable(),
                TextColumn::make('teacher.name')->label('Teacher')->sortable()->searchable(),
                BooleanColumn::make('is_published')->label('Published')->sortable(),
                BadgeColumn::make('price')->label('Price (UZS)')->sortable(),
                BadgeColumn::make('discount')->label('Discount (%)')->sortable(),
                ImageColumn::make('image')
                    ->label('Image')
                    ->circular()
                    ->url(fn(Course $record) => Storage::url($record->image)),
                TextColumn::make('created_at')->label('Created At')->date()->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->disabled(fn() => !auth()->user()?->can('course.edit')),
                Tables\Actions\DeleteAction::make()->disabled(fn() => !auth()->user()?->can('course.delete')),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourses::route('/create'),
            'edit' => Pages\EditCourses::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [];
    }
}
