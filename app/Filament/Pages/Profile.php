<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class Profile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static string $view = 'filament.pages.profile';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 4;

    public ?array $data = [];

    /*
    |----------------------------------------------------------------------
    | Access Control
    |----------------------------------------------------------------------
    | Determines if the authenticated user has permission to access this page.
    */
    public static function canAccess(string $permission = 'view'): bool
    {
        if (!$user = auth()->user())
            return false;

        return match ($permission) {
            'view' => $user->can('profile.view'),
            'edit' => $user->can('profile.edit'),
            'delete' => $user->can('profile.delete'),
            default => false,
        };
    }

    /*
    |----------------------------------------------------------------------
    | Form Schema Definition
    |----------------------------------------------------------------------
    | Defines the structure and fields of the profile editing form.
    */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('My Profile')
                    ->description('Update your account details below.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255)
                            ->disabled(fn() => !$this->canAccess('edit')),

                        TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->required()
                            ->unique('users', 'email', ignorable: Auth::user())
                            ->maxLength(255)
                            ->disabled(fn() => !$this->canAccess('edit')),
                    ])
                    ->collapsible(),

                Section::make('Change Password')
                    ->description('Change your current password securely.')
                    ->schema([
                        TextInput::make('current_password')
                            ->label('Current Password')
                            ->password()
                            ->required(fn($get) => filled($get('password')))
                            ->dehydrated(false)
                            ->helperText('Enter your current password to change it.')
                            ->disabled(fn() => !$this->canAccess('edit')),

                        TextInput::make('password')
                            ->label('New Password')
                            ->password()
                            ->minLength(8)
                            ->dehydrated(fn(?string $state): bool => filled($state))
                            ->helperText('Leave blank to keep your current password.')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (empty($state)) {
                                    $set('password_confirmation', null);
                                    $set('current_password', null);
                                }
                            })
                            ->disabled(fn() => !$this->canAccess('edit')),

                        TextInput::make('password_confirmation')
                            ->label('Confirm Password')
                            ->password()
                            ->required(fn($get) => filled($get('password')))
                            ->same('password')
                            ->dehydrated(fn(?string $state): bool => filled($state))
                            ->disabled(fn() => !$this->canAccess('edit')),
                    ])
                    ->collapsible(),

                Actions::make([
                    Action::make('save')
                        ->label('Save Changes')
                        ->action('save')
                        ->color('primary')
                        ->visible(fn() => $this->canAccess('edit')),

                    Action::make('delete')
                        ->label('Delete Profile')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Confirm Profile Deletion')
                        ->modalSubmitActionLabel('Delete Profile')
                        ->modalWidth('md')
                        ->form([
                            TextInput::make('delete_password')
                                ->label('Enter your password to confirm')
                                ->required()
                                ->password()
                                ->autocomplete('current-password'),
                        ])
                        ->action(fn($data) => $this->delete($data))
                        ->visible(fn() => $this->canAccess('delete')),
                ])->fullWidth(),
            ])
            ->statePath('data')
            ->model(Auth::user());
    }

    /*
    |----------------------------------------------------------------------
    | Page Initialization
    |----------------------------------------------------------------------
    | Fills the form with the authenticated user's current data on page load.
    */
    public function mount(): void
    {
        if (Auth::check()) {
            $this->form->fill([
                'name' => Auth::user()->name,
                'email' => Auth::user()->email,
            ]);
        }
    }

    /*
    |----------------------------------------------------------------------
    | Save Profile Changes
    |----------------------------------------------------------------------
    | Validates and updates the user's profile data, including password if provided.
    */
    public function save(): void
    {
        $data = $this->form->getState();

        if (!empty($data['password'])) {
            if (!Hash::check($data['current_password'] ?? '', Auth::user()->password)) {
                Notification::make()
                    ->title('Error')
                    ->body('Your current password is incorrect.')
                    ->danger()
                    ->send();
                return;
            }
        }

        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        Auth::user()->update($updateData);

        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        Notification::make()
            ->title('Success')
            ->body('Your profile has been updated successfully!')
            ->success()
            ->send();

        $this->redirect('/login');
    }

    /*
    |---------------------------------------------------------------------- 
    | Delete Profile
    |---------------------------------------------------------------------- 
    | Validates the entered password and deletes the user's profile if correct. 
    | Logs the user out after deletion and redirects to the login page.
    */
    public function delete($data): void
    {
        if (!Hash::check($data['delete_password'], Auth::user()->password)) {
            Notification::make()
                ->title('Error')
                ->body('The password you entered is incorrect.')
                ->danger()
                ->send();
            return;
        }

        $user = Auth::user();
        Auth::logout();
        $user->delete();

        Notification::make()
            ->title('Profile Deleted')
            ->body('Your profile has been deleted successfully.')
            ->success()
            ->send();

        $this->redirect('/login');
    }

    /*
    |---------------------------------------------------------------------- 
    | Form Initialization
    |---------------------------------------------------------------------- 
    | Initializes the form with the user's data for profile update.
    */
    protected function getForms(): array
    {
        return [
            'form' => $this->form($this->makeForm()),
        ];
    }
}
