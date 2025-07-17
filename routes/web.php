<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IdeasController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Home page redirect to ideas
Route::get('/', function () {
    return redirect()->route('ideas.index');
});

// Ideas routes
Route::get('/ideas', [IdeasController::class, 'index'])->name('ideas.index');

// Optional: API routes for AJAX calls
Route::prefix('api')->group(function () {
    Route::get('/ideas', [IdeasController::class, 'index'])->name('api.ideas.index');
});

// Optional: Clear cache route (only for development)
if (app()->environment('local')) {
    Route::get('/clear-cache', function () {
        \Illuminate\Support\Facades\Cache::flush();
        return response()->json(['message' => 'Cache cleared successfully']);
    });
}
