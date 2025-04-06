<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Certificate extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static string $view = 'filament.pages.certificate';
    protected static ?string $navigationGroup = 'Documents';
    protected static ?int $navigationSort = 9;
}
