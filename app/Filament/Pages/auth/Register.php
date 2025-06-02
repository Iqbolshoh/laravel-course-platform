<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Register as BaseRegister;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Checkbox;

class Register extends BaseRegister
{
    protected function getFormSchema(): array
    {
        return [
            TextInput::make('name')
                ->label('Full Name')
                ->required()
                ->placeholder('Enter your full name')
                ->maxLength(255),

            TextInput::make('email')
                ->label('Email Address')
                ->required()
                ->email()
                ->placeholder('Enter your email address')
                ->maxLength(255),

            TextInput::make('password')
                ->label('Password')
                ->password()
                ->required()
                ->minLength(8)
                ->placeholder('Enter your password'),

            TextInput::make('password_confirmation')
                ->label('Confirm Password')
                ->password()
                ->required()
                ->minLength(8)
                ->same('password')
                ->placeholder('Confirm your password'),

            Checkbox::make('remember')
                ->label('Remember Me'),
        ];
    }

    protected function handleRegistration(array $data): \Illuminate\Database\Eloquent\Model
    {
        $ROLE = 'user'; // DEFOULT ROLE

        $user = \App\Models\User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        $user->assignRole($ROLE);

        return $user;
    }
}
