<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class Home extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.pages.home';
    protected static ?string $slug = '/';
    protected static ?int $navigationSort = 1;

    /*
    |----------------------------------------------------------------------
    | getViewData
    |----------------------------------------------------------------------
    | Retrieves the current user's name, email, role, join date, and 
    | last login time.
    */
    public function getViewData(): array
    {
        $user = Auth::user();

        return [
            'name' => $user->name,
            'email' => $user->email,
            'role' => optional($user->roles->first())->name,
            'joined' => $user->created_at->format('d M, Y'),
            'lastLogin' => $user->last_login_at ? $user->last_login_at->format('d M, Y H:i') : 'Not available',
        ];
    }
}