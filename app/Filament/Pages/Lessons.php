<?php

namespace App\Filament\Pages;

use App\Models\LessonModel;
use App\Models\CourseModel;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Actions\Action as FilamentAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Helpers\Utils;
use Illuminate\Support\Facades\Storage;

class Lessons extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static string $view = 'filament.pages.lessons';
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

    protected function getTableQuery()
    {
        return LessonModel::query()->with('course');
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('id')->label('ID')->sortable(),
            TextColumn::make('course.title')->label('Course')->sortable()->searchable(),
            TextColumn::make('title')->label('Lesson Title')->sortable()->searchable(),
            TextColumn::make('video_url')->label('Video URL')->limit(30),
            TextColumn::make('order')->label('Order')->sortable(),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn() => self::canAccess('edit'))
                ->form($this->getFormSchema())
                ->action(function (LessonModel $lesson, array $data): void {
                    $lesson->update($data);
                    Utils::notify('Success', "Lesson '{$lesson->title}' updated!", 'success');
                }),

            DeleteAction::make()
                ->visible(fn() => self::canAccess('delete'))
                ->action(function (LessonModel $lesson): void {
                    $lesson->delete();
                    Utils::notify('Deleted', "Lesson '{$lesson->title}' deleted!", 'danger');
                }),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return self::canAccess('delete')
            ? [
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function ($records): void {
                            foreach ($records as $lesson) {
                                $lesson->delete();
                            }
                            session()->flash('success', 'Selected lessons deleted successfully!');
                        }),
                ]),
            ]
            : [];
    }

    protected function getHeaderActions(): array
    {
        return [
            FilamentAction::make('create')
                ->label('Create Lesson')
                ->icon('heroicon-o-plus')
                ->form($this->getFormSchema())
                ->action(function (array $data): void {
                    LessonModel::create($data);
                    Utils::notify('Success', "Lesson '{$data['title']}' created!", 'success');
                })
                ->visible(fn() => self::canAccess('create'))
                ->color('primary'),
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('course_id')
                ->label('Course')
                ->required()
                ->searchable()
                ->options(CourseModel::all()->pluck('title', 'id')),

            TextInput::make('title')
                ->label('Lesson Title')
                ->required(),

            TextInput::make('video_url')
                ->label('Video URL')
                ->required()
                ->url(),

            Textarea::make('content')
                ->label('Lesson Content')
                ->rows(5),

            TextInput::make('order')
                ->label('Display Order')
                ->numeric()
                ->default(0),
        ];
    }
}