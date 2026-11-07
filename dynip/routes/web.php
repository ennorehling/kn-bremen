<?php

use App\Http\Controllers\LegacyController;
use Illuminate\Support\Facades\Route;

Route::controller(LegacyController::class)->name('legacy.')->group(function () {
    Route::post('/dynip.php', 'submit')->name('submit');
});

Route::get('/', function () {
    return view('welcome');
});
