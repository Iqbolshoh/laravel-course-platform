<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Course extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static string $view = 'filament.pages.course';
    protected static ?string $navigationGroup = 'Education';
    protected static ?int $navigationSort = 6;
}
