<?php

use App\Http\Controllers\Backend\RoleController;
use App\Http\Controllers\Backend\UserController;
use App\Http\Middleware\EnsureRbacEnabled;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', EnsureRbacEnabled::class])->group(function (): void {
    Route::resource('users', UserController::class)->except('show');
    Route::resource('roles', RoleController::class)->except('show');
});
