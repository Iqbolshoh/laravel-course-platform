<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Task extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static string $view = 'filament.pages.task';
    protected static ?string $navigationGroup = 'Education';
    protected static ?int $navigationSort = 8;
}
