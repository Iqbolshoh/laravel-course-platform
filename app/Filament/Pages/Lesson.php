<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Lesson extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static string $view = 'filament.pages.lesson';
    protected static ?string $navigationGroup = 'Education';
    protected static ?int $navigationSort = 7;
}
