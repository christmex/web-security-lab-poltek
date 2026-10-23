<?php

use App\Http\Controllers\Api\EncryptedProfileController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| SECURITY LAB — BRANCH 3: encrypted-id profile API (challenge)
|--------------------------------------------------------------------------
| The id in the URL is encrypted with the leaked salt. A plain numeric id
| returns a hint. Recover the salt (branch 2) to forge any identifier.
*/
Route::middleware('auth')->get('/api/profile/{identifier}', [EncryptedProfileController::class, 'show'])
    ->name('api.profile.show');

require __DIR__.'/settings.php';
