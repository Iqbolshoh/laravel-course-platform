<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CoursesResource\Pages;
use App\Models\Course;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\BadgeColumn;
use Storage;

class CoursesResource extends Resource
{
    protected static ?string $model = Course::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'Education';
    protected static ?int $navigationSort = 6;

    public static function canAccess(string $permission = 'view'): bool
    {
        if (!$user = auth()->user())
            return false;

        return match ($permission) {
            'view' => $user->can('course.view'),
            'create' => $user->can('course.create'),
            'edit' => $user->can('course.edit'),
            'delete' => $user->can('course.delete'),
            default => false,
        };
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->label('Course Title'),

                Textarea::make('description')
                    ->label('Description')
                    ->rows(5)
                    ->maxLength(65535),

                FileUpload::make('image')
                    ->image()
                    ->imageEditor()
                    ->directory('courses')
                    ->label('Course Image'),

                Toggle::make('is_published')
                    ->label('Published'),

                TextInput::make('price')
                    ->numeric()
                    ->required()
                    ->label('Price (UZS)'),

                TextInput::make('discount')
                    ->numeric()
                    ->label('Discount (%)')
                    ->rules('nullable|numeric|min:0|max:100'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('teacher.name')->sortable()->label('Teacher')->searchable(),
                BooleanColumn::make('is_published')->sortable()->label('Published'),
                BadgeColumn::make('price')->sortable()->label('Price (UZS)'),
                BadgeColumn::make('discount')->sortable()->label('Discount (%)'),
                ImageColumn::make('image')
                    ->label('Image')
                    ->circular()
                    ->url(fn(Course $record) => Storage::url($record->image)),
                TextColumn::make('created_at')->date()->sortable()->label('Created At'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourses::route('/create'),
            'edit' => Pages\EditCourses::route('/{record}/edit'),
        ];
    }
}
