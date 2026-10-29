<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/cake-studio')->name('home');

// Where sign-in lands: the admin home for admins, the shop for customers.
Route::get('dashboard', fn () => request()->user()?->is_admin ? to_route('admin.home') : to_route('cake-studio.index'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

require __DIR__.'/shop.php';
require __DIR__.'/admin.php';
require __DIR__.'/settings.php';
