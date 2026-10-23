<?php

use App\Http\Controllers\Api\PublicProfileController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

/*
|--------------------------------------------------------------------------
| SECURITY LAB — BRANCH 1: public IDOR profile API (intentionally vulnerable)
|--------------------------------------------------------------------------
| No auth. Walk the id to read any user, including their plaintext reset token.
*/
Route::get('/api/profile/{id}', [PublicProfileController::class, 'show'])
    ->name('api.profile.show');

Route::middleware(['auth'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
