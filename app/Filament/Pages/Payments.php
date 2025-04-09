<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Payments extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static string $view = 'filament.pages.payments';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 10;
}
