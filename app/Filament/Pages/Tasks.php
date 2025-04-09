<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Tasks extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static string $view = 'filament.pages.tasks';
    protected static ?string $navigationGroup = 'Education';
    protected static ?int $navigationSort = 8;
}
