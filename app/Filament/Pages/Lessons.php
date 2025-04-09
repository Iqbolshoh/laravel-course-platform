<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Lessons extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static string $view = 'filament.pages.lessons';
    protected static ?string $navigationGroup = 'Education';
    protected static ?int $navigationSort = 7;
}
