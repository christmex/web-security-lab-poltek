<?php

use App\Http\Controllers\Api\AuthenticatedProfileController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| SECURITY LAB — BRANCH 2: auth-gated IDOR profile API (intentionally vulnerable)
|--------------------------------------------------------------------------
| Login required, but no per-object authorization -> IDOR persists. Response
| also leaks `salt`, the breadcrumb for branch 3.
*/
Route::middleware('auth')->get('/api/profile/{id}', [AuthenticatedProfileController::class, 'show'])
    ->name('api.profile.show');

require __DIR__.'/settings.php';
