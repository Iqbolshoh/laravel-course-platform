<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Actions\Action as FilamentAction;
use App\Models\CourseModel;

class Course extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static string $view = 'filament.pages.course';
    protected static ?string $navigationGroup = 'Education';
    protected static ?int $navigationSort = 6;

    /*
    |-------------------------------------------------------------------------- 
    | Get Table Query
    |-------------------------------------------------------------------------- 
    | Fetches the query for the CourseModel to populate the table.
    */
    protected function getTableQuery()
    {
        return CourseModel::query();
    }

    /*
    |-------------------------------------------------------------------------- 
    | Get Table Columns
    |-------------------------------------------------------------------------- 
    | Defines the columns to display in the table.
    */
    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('id')->label('ID')->sortable(),
            TextColumn::make('title')->label('Course Name')->sortable()->searchable(),
            TextColumn::make('description')->label('Description')->limit(50)->sortable()->searchable(),
            TextColumn::make('created_at')->label('Created At')->sortable()->dateTime(),
            TextColumn::make('updated_at')->label('Updated At')->sortable()->dateTime(),
        ];
    }

    /*
    |-------------------------------------------------------------------------- 
    | Get Table Actions
    |-------------------------------------------------------------------------- 
    | Defines the actions available in the table, like Edit and Delete.
    */
    protected function getTableActions(): array
    {
        return [
            EditAction::make()
                ->form($this->getEditFormSchema())
                ->action(function (CourseModel $course, array $data): void {
                    $this->updateCourse($course, $data);
                }),

            DeleteAction::make()
                ->action(function (CourseModel $course): void {
                    $course->delete();
                    session()->flash('success', "Course '{$course->title}' deleted successfully!");
                }),
        ];
    }

    /*
    |-------------------------------------------------------------------------- 
    | Get Create Form Schema
    |-------------------------------------------------------------------------- 
    | Defines the fields for the "Create Course" form.
    */
    protected function getCreateFormSchema(): array
    {
        return [
            TextInput::make('title')
                ->label('Course Title')
                ->required()
                ->maxLength(255),

            Textarea::make('description')
                ->label('Course Description')
                ->required()
                ->maxLength(500),

            FileUpload::make('image')
                ->label('Course Image')
                ->image()
                ->disk('public')
                ->directory('course_images')
                ->nullable()
                ->maxSize(5 * 1024)
                ->helperText('Upload a course image. (Max size: 5MB)'),
        ];
    }

    /*
    |-------------------------------------------------------------------------- 
    | Get Edit Form Schema
    |-------------------------------------------------------------------------- 
    | Defines the fields for the "Edit Course" form.
    */
    protected function getEditFormSchema(): array
    {
        return [
            TextInput::make('title')
                ->label('Course Title')
                ->required()
                ->maxLength(255),

            Textarea::make('description')
                ->label('Course Description')
                ->required()
                ->maxLength(500),

            FileUpload::make('image')
                ->label('Course Image')
                ->image()
                ->disk('public')
                ->directory('course_images')
                ->nullable()
                ->maxSize(5 * 1024)
                ->helperText('Upload a course image. (Max size: 5MB)'),
        ];
    }

    /*
    |-------------------------------------------------------------------------- 
    | Create Course Method
    |-------------------------------------------------------------------------- 
    | Handles creating a new course, including uploading an image.
    */
    private function createCourse(array $data): void
    {
        $imagePath = null;
        if ($data['image'] && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
            $imagePath = $data['image']->store('course_images', 'public');
        }

        CourseModel::create([
            'title' => $data['title'],
            'description' => $data['description'],
            'image' => $imagePath,
            'teacher_id' => auth()->id(),
            'is_published' => false,
        ]);
        session()->flash('success', "Course '{$data['title']}' created successfully!");
    }


    /*
    |-------------------------------------------------------------------------- 
    | Update Course Method
    |-------------------------------------------------------------------------- 
    | Handles updating an existing course, including uploading an image if provided.
    */
    private function updateCourse(CourseModel $course, array $data): void
    {
        $imagePath = $data['image'] ? $data['image']->store('course_images', 'public') : $course->image;

        $course->update([
            'title' => $data['title'],
            'description' => $data['description'],
            'image' => $imagePath,
        ]);
        session()->flash('success', "Course '{$data['title']}' updated successfully!");
    }

    /*
    |-------------------------------------------------------------------------- 
    | Get Header Actions
    |-------------------------------------------------------------------------- 
    | Defines the actions available at the header, like creating a new course.
    */
    protected function getHeaderActions(): array
    {
        return [
            FilamentAction::make('create')
                ->label('Create Course')
                ->icon('heroicon-o-plus')
                ->form($this->getCreateFormSchema())
                ->action(function (array $data): void {
                    $this->createCourse($data);
                })
                ->color('primary'),
        ];
    }
}
