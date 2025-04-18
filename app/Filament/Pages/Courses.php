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
use Filament\Tables\Actions\Action;
use Filament\Actions\Action as FilamentAction;
use App\Models\CourseModel;
use Illuminate\Support\Facades\Storage;
use App\Helpers\Utils;

class Courses extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static string $view = 'filament.pages.courses';
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

    protected function getTableQuery()
    {
        return CourseModel::query();
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('id')->label('ID')->sortable(),
            TextColumn::make('title')->label('Course Name')->sortable()->searchable(),
            TextColumn::make('description')->label('Description')->limit(50)->sortable()->searchable(),
            TextColumn::make('created_at')->label('Created At')->sortable()->dateTime(),
            TextColumn::make('updated_at')->label('Updated At')->sortable()->dateTime(),
            \Filament\Tables\Columns\ImageColumn::make('image')
                ->label('Image')
                ->disk('public')
                ->height(50)
                ->circular(),
            TextColumn::make('discount')->label('Discount (%)')->sortable()->numeric()->default(0),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Action::make('view')
                ->label('View')
                ->icon('heroicon-o-eye')
                ->url(fn(CourseModel $record) => route('courses.show', $record))
                ->openUrlInNewTab(), // Yoki olib tashlang agar yangi tabda ochilishini istamasangiz

            EditAction::make()
                ->visible(fn() => $this->canAccess('edit'))
                ->form($this->getEditFormSchema())
                ->action(fn(CourseModel $course, array $data) => $this->updateCourse($course, $data)),

            DeleteAction::make()
                ->visible(fn() => $this->canAccess('delete'))
                ->action(function (CourseModel $course): void {
                    if ($course->image && Storage::disk('public')->exists($course->image)) {
                        Storage::disk('public')->delete($course->image);
                    }
                    $course->delete();
                    Utils::notify('Success', "Course '{$course->title}' deleted successfully!", 'success');
                }),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return $this->canAccess('delete')
            ? [
                \Filament\Tables\Actions\BulkActionGroup::make([

                    \Filament\Tables\Actions\DeleteBulkAction::make()
                        ->action(function ($records): void {
                            foreach ($records as $course) {
                                if ($course->image && Storage::disk('public')->exists($course->image)) {
                                    Storage::disk('public')->delete($course->image);
                                }
                                $course->delete();
                            }
                            session()->flash('success', 'Selected courses deleted successfully!');
                        }),
                ]),
            ]
            : [];
    }

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
                ->visible(fn() => self::canAccess('create'))
                ->color('primary'),
        ];
    }

    protected function getCreateFormSchema(): array
    {
        return [
            TextInput::make('title')
                ->label('Course Title')
                ->required()
                ->maxLength(255)
                ->rules('min:3', 'max:255'),

            Textarea::make('description')
                ->label('Course Description')
                ->required()
                ->maxLength(500)
                ->rules('nullable', 'max:500'),

            FileUpload::make('image')
                ->label('Course Image')
                ->image()
                ->disk('public')
                ->directory('course_images')
                ->nullable()
                ->maxSize(5 * 1024)
                ->helperText('Upload a course image. (Max size: 5MB)'),

            TextInput::make('price')
                ->label('Price')
                ->required()
                ->numeric()
                ->rules('min:0', 'max:9999999')
                ->helperText('Enter the course price'),

            TextInput::make('discount')
                ->label('Discount (%)')
                ->nullable()
                ->numeric()
                ->rules('min:0', 'max:100') // Validation for discount percentage
                ->helperText('Enter the discount percentage'),
        ];
    }

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

            TextInput::make('price')
                ->label('Price')
                ->required()
                ->numeric()
                ->helperText('Enter the course price'),

            TextInput::make('discount')
                ->label('Discount (%)')
                ->nullable()
                ->numeric()
                ->rules('min:0', 'max:100')
                ->helperText('Enter the discount percentage'),
        ];
    }

    private function createCourse(array $data): void
    {
        $validatedData = \Validator::make($data, [
            'title' => 'required|max:255',
            'description' => 'nullable|max:500',
            'price' => 'required|numeric|min:0|max:9999999',
            'discount' => 'nullable|numeric|min:0|max:100', // Validate discount as well
        ])->validate();

        if ($data['image'] && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
            $data['image']->store('course_images', 'public');
        }

        CourseModel::create([
            'title' => $data['title'],
            'description' => $data['description'],
            'image' => $data['image'],
            'price' => $data['price'],
            'discount' => $data['discount'] ?? 0, // If no discount, set to 0
            'teacher_id' => auth()->id(),
            'is_published' => false,
        ]);

        Utils::notify('Success', "Course '{$data['title']}' created successfully!", 'success');
    }

    private function updateCourse(CourseModel $course, array $data): void
    {
        $validatedData = \Validator::make($data, [
            'title' => 'required|max:255',
            'description' => 'nullable|max:500',
            'price' => 'required|numeric|min:0|max:9999999',
            'discount' => 'nullable|numeric|min:0|max:100',
        ])->validate();

        $updateData = [
            'title' => $data['title'],
            'description' => $data['description'],
            'price' => $data['price'],
            'discount' => $data['discount'] ?? 0,
        ];

        $course->update($updateData);
        Utils::notify('Success', "Course '{$course->title}' updated successfully!", 'success');
    }

    public function details($courseId)
    {
        $course = CourseModel::findOrFail($courseId);

        return [
            $courseId
        ];
    }
}
