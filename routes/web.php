<?php

use App\Http\Controllers\Status\StatusController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\TeamController;
use App\Livewire\Admin\AdminDashboard;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/', AdminDashboard::class)->name('admin.dashboard');
    Route::get('/teams/qr-codes', [AdminController::class, 'downloadTeamQrCodes'])->name('admin.teams.qr-codes');
});

// Status routes
Route::get('/status/{competition}', [StatusController::class, 'show'])->name('status.show');
Route::get('/events/competition/{competition}', [StatusController::class, 'events'])->name('status.events');

// Team routes
Route::get('/t/{team:slug}', [TeamController::class, 'show'])->name('team.show');
Route::post('/t/{team:slug}/submit', [TeamController::class, 'submit'])->name('team.submit');

require __DIR__.'/auth.php';
