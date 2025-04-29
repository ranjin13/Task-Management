<?php

use App\Http\Controllers\TaskController;
use App\Http\Controllers\SubtaskController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Task routes
Route::middleware(['auth'])->group(function () {
    // Task trash routes
    Route::get('tasks/trashed', [TaskController::class, 'trashed'])->name('tasks.trashed');
    Route::post('tasks/{id}/restore', [TaskController::class, 'restore'])->name('tasks.restore');
    Route::delete('tasks/{id}/force-delete', [TaskController::class, 'forceDelete'])->name('tasks.force-delete');
    
    Route::resource('tasks', TaskController::class);
    Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');
    Route::patch('tasks/{task}/toggle-published', [TaskController::class, 'togglePublished'])->name('tasks.toggle-published');
    
    // Subtask routes
    Route::post('subtasks', [SubtaskController::class, 'store'])->name('subtasks.store');
    Route::put('subtasks/{subtaskId}', [SubtaskController::class, 'toggle'])->name('subtasks.toggle');
    Route::delete('subtasks/{subtaskId}', [SubtaskController::class, 'destroy'])->name('subtasks.destroy');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
