<?php

namespace App\Helpers;

use Filament\Notifications\Notification;

class Utils
{
    public static function notify(string $title, string $message, string $type = 'success')
    {
        Notification::make()->title($title)->body($message)->{$type}()->send();
    }
}
