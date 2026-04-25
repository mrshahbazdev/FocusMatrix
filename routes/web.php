<?php

use App\Http\Controllers\AcceptInvitationController;
use App\Http\Controllers\AiController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DelegationController;
use App\Http\Controllers\IcsController;
use App\Http\Controllers\IntegrationController;
use App\Http\Controllers\KillListController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\OrgCheckController;
use App\Http\Controllers\SelfCheckController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TriageController;
use App\Http\Controllers\VoiceController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Mail\Message;

Route::get('/', LandingController::class)->name('landing');

Route::get('/test-email', function () {
    try {
        Mail::raw('This is a test email from FocusMatrix to verify SMTP configuration is working correctly.', function (Message $message) {
            $message->to('mrshahbaznns@gmail.com')
                    ->subject('FocusMatrix SMTP Test - Configuration Verified');
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Test email sent successfully to mrshahbaznns@gmail.com',
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status'  => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
})->name('test.email');

Route::get('/legal/impressum', [LegalController::class, 'impressum'])->name('legal.impressum');
Route::get('/legal/privacy', [LegalController::class, 'privacy'])->name('legal.privacy');
Route::get('/legal/terms', [LegalController::class, 'terms'])->name('legal.terms');
Route::get('/legal/cookies', [LegalController::class, 'cookies'])->name('legal.cookies');

Route::get('/calendar/ics/{token}', [IcsController::class, 'feed'])->name('calendar.ics');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('tasks', TaskController::class)->except(['create', 'edit']);
    Route::get('/tasks/{task}/triage', [TriageController::class, 'show'])->name('tasks.triage');
    Route::post('/tasks/{task}/triage', [TriageController::class, 'decide'])->name('tasks.triage.decide');

    Route::get('/assigned', [DelegationController::class, 'assignedIndex'])->name('delegations.assigned');
    Route::post('/delegations/{delegation}/accept', [DelegationController::class, 'accept'])->name('delegations.accept');
    Route::post('/delegations/{delegation}/decline', [DelegationController::class, 'decline'])->name('delegations.decline');
    Route::get('/assigned/{token}/accept', [DelegationController::class, 'acceptByToken'])->name('delegations.accept.token');
    Route::get('/assigned/{token}/decline', [DelegationController::class, 'declineByToken'])->name('delegations.decline.token');
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

    Route::post('/integrations/webhook/{provider}', [IntegrationController::class, 'connectWebhook'])->name('integrations.webhook.connect');
    Route::post('/integrations/webhook/{provider}/test', [IntegrationController::class, 'testWebhook'])->name('integrations.webhook.test');
    Route::delete('/integrations/webhook/{provider}', [IntegrationController::class, 'disconnectWebhook'])->name('integrations.webhook.disconnect');
    Route::post('/integrations/ics/regenerate', [IntegrationController::class, 'regenerateIcsToken'])->name('integrations.ics.regenerate');

    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::post('/calendar/events', [CalendarController::class, 'storeEvent'])->name('calendar.events.store');
    Route::patch('/calendar/events/{event}', [CalendarController::class, 'updateEvent'])->name('calendar.events.update');
    Route::delete('/calendar/events/{event}', [CalendarController::class, 'destroyEvent'])->name('calendar.events.destroy');
    Route::post('/calendar/focus-block/{task}', [CalendarController::class, 'focusBlock'])->name('calendar.focus-block');
    Route::post('/calendar/import-weak', [CalendarController::class, 'importWeakToInbox'])->name('calendar.import-weak');

    Route::get('/settings/ai', [AiController::class, 'index'])->name('ai.index');
    Route::put('/settings/ai', [AiController::class, 'update'])->name('ai.update');
    Route::post('/settings/ai/test', [AiController::class, 'test'])->name('ai.test');
    Route::delete('/settings/ai', [AiController::class, 'destroy'])->name('ai.destroy');

    Route::post('/tasks/{task}/ai-suggest', [TriageController::class, 'aiSuggest'])->name('tasks.ai-suggest');
    Route::post('/delegations/draft', [DelegationController::class, 'aiDraft'])->name('delegations.ai-draft');
    Route::post('/self-check/insights', [SelfCheckController::class, 'aiInsights'])->name('self-check.ai-insights');

    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::get('/billing/checkout/{plan}', [BillingController::class, 'checkout'])->name('billing.checkout');
    Route::get('/billing/portal', [BillingController::class, 'portal'])->name('billing.portal');
    Route::post('/billing/cancel', [BillingController::class, 'cancel'])->name('billing.cancel');
    Route::post('/billing/resume', [BillingController::class, 'resume'])->name('billing.resume');

    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

    Route::post('/voice/transcribe', [VoiceController::class, 'transcribe'])->name('voice.transcribe');

    Route::get('/gdpr/export', [LegalController::class, 'exportData'])->name('gdpr.export');
    Route::post('/gdpr/delete', [LegalController::class, 'requestDeletion'])->name('gdpr.delete');

    Route::get('/accept-invitation/{invitation}', [AcceptInvitationController::class, 'accept'])
        ->name('accept-invitation');
});
