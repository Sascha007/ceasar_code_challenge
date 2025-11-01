<?php

use App\Http\Controllers\Status\StatusController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('status.latest');
});

// Status routes
Route::get('/status/latest', function () {
    $competition = \App\Models\Competition::latest()->firstOrFail();
    return redirect()->route('status.show', $competition);
})->name('status.latest');

Route::get('/status/{competition}', [StatusController::class, 'show'])->name('status.show');
Route::get('/events/competition/{competition}', [StatusController::class, 'events'])->name('status.events');
