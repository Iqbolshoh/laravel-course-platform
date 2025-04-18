<?php

use Illuminate\Support\Facades\Route;
use App\Filament\Pages;

Route::middleware('auth')->group(function () {
    Route::get('/courses/{course}', [Pages\Courses::class, 'details'])->name('courses.show');
});
