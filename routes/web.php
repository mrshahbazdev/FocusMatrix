<?php

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DelegationController;
use App\Http\Controllers\IntegrationController;
use App\Http\Controllers\KillListController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\OrgCheckController;
use App\Http\Controllers\SelfCheckController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TriageController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class)->name('landing');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('tasks', TaskController::class)->except(['create', 'edit']);
    Route::get('/tasks/{task}/triage', [TriageController::class, 'show'])->name('tasks.triage');
    Route::post('/tasks/{task}/triage', [TriageController::class, 'decide'])->name('tasks.triage.decide');

    Route::resource('delegations', DelegationController::class)->except(['edit']);

    Route::get('/kill-list', [KillListController::class, 'index'])->name('kill-list.index');
    Route::post('/kill-list', [KillListController::class, 'store'])->name('kill-list.store');
    Route::delete('/kill-list/{item}', [KillListController::class, 'destroy'])->name('kill-list.destroy');

    Route::get('/self-check', [SelfCheckController::class, 'index'])->name('self-check.index');
    Route::post('/self-check', [SelfCheckController::class, 'store'])->name('self-check.store');

    Route::get('/org-check', [OrgCheckController::class, 'index'])->name('org-check.index');
    Route::post('/org-check', [OrgCheckController::class, 'store'])->name('org-check.store');

    Route::get('/integrations', [IntegrationController::class, 'index'])->name('integrations.index');
    Route::get('/integrations/google/connect', [IntegrationController::class, 'connectGoogle'])->name('integrations.google.connect');
    Route::get('/integrations/google/callback', [IntegrationController::class, 'callbackGoogle'])->name('integrations.google.callback');
    Route::delete('/integrations/google', [IntegrationController::class, 'disconnectGoogle'])->name('integrations.google.disconnect');

    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::post('/calendar/focus-block/{task}', [CalendarController::class, 'focusBlock'])->name('calendar.focus-block');
    Route::post('/calendar/import-weak', [CalendarController::class, 'importWeakToInbox'])->name('calendar.import-weak');
});
