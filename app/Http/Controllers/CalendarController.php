<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use App\Models\Integration;
use App\Models\Task;
use App\Services\GoogleCalendarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class CalendarController extends Controller
{
    public function __construct(private GoogleCalendarService $google)
    {
    }

    public function index(Request $request): Response
    {
        $user = $request->user();

        // Determine month window (defaults to current month)
        $monthParam = $request->query('month'); // format: YYYY-MM
        $anchor = $monthParam
            ? Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth()
            : now()->startOfMonth();
        $gridStart = (clone $anchor)->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $gridEnd = (clone $anchor)->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        // Native events in window
        $nativeEvents = CalendarEvent::where('user_id', $user->id)
            ->whereBetween('starts_at', [$gridStart, $gridEnd])
            ->orderBy('starts_at')
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'type' => 'native',
                'source' => $e->source,
                'title' => $e->title,
                'description' => $e->description,
                'location' => $e->location,
                'color' => $e->color,
                'all_day' => $e->all_day,
                'starts_at' => $e->starts_at->toIso8601String(),
                'ends_at' => $e->ends_at->toIso8601String(),
                'task_id' => $e->task_id,
            ]);

        // Task due dates as virtual events (Keep tasks with a due_at)
        $taskDue = Task::where('user_id', $user->id)
            ->whereNotNull('due_at')
            ->whereBetween('due_at', [$gridStart, $gridEnd])
            ->get()
            ->map(fn ($t) => [
                'id' => 'task-' . $t->id,
                'type' => 'task',
                'source' => 'task_due',
                'title' => '📌 ' . $t->title,
                'description' => $t->description,
                'color' => match ($t->status) {
                    Task::STATUS_KEEP => 'emerald',
                    Task::STATUS_DELEGATE => 'accent',
                    Task::STATUS_DROP => 'rose',
                    default => 'graphite',
                },
                'all_day' => true,
                'starts_at' => $t->due_at->toIso8601String(),
                'ends_at' => $t->due_at->copy()->addMinutes(30)->toIso8601String(),
                'task_id' => $t->id,
                'task_status' => $t->status,
            ]);

        // Focus-blocked tasks (if any)
        $focusBlocks = Task::where('user_id', $user->id)
            ->whereNotNull('focused_block_at')
            ->whereBetween('focused_block_at', [$gridStart, $gridEnd])
            ->get()
            ->map(fn ($t) => [
                'id' => 'focus-' . $t->id,
                'type' => 'focus',
                'source' => 'focus_block',
                'title' => '🎯 ' . $t->title,
                'color' => 'navy',
                'all_day' => false,
                'starts_at' => $t->focused_block_at->toIso8601String(),
                'ends_at' => $t->focused_block_at->copy()->addMinutes(60)->toIso8601String(),
                'task_id' => $t->id,
            ]);

        // Optional Google overlay
        $integration = $user->integrations()
            ->where('provider', Integration::PROVIDER_GOOGLE)
            ->first();
        $googleEvents = collect();
        $weak = [];
        if ($integration) {
            $raw = $this->google->upcomingEvents($integration, 96);
            $weak = collect($raw)->filter(fn ($e) => count($e['flags']) > 0)->values()->all();
            $googleEvents = collect($raw)->map(fn ($e) => [
                'id' => 'g-' . ($e['id'] ?? md5($e['title'] . ($e['start'] ?? ''))),
                'type' => 'google',
                'source' => 'google',
                'title' => '📅 ' . $e['title'],
                'color' => 'amber',
                'all_day' => false,
                'starts_at' => $e['start'] ?? null,
                'ends_at' => $e['end'] ?? null,
                'flags' => $e['flags'] ?? [],
                'attendees' => $e['attendees'] ?? null,
            ])->filter(fn ($e) => $e['starts_at']);
        }

        $all = $nativeEvents
            ->concat($taskDue)
            ->concat($focusBlocks)
            ->concat($googleEvents)
            ->values();

        return Inertia::render('Calendar/Index', [
            'connected' => (bool) $integration,
            'account_email' => $integration?->account_email,
            'events' => $all,
            'weak_meetings' => $weak,
            'month_anchor' => $anchor->format('Y-m'),
            'grid_start' => $gridStart->toIso8601String(),
            'grid_end' => $gridEnd->toIso8601String(),
            'today' => now()->toIso8601String(),
        ]);
    }

    public function storeEvent(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'in:' . implode(',', CalendarEvent::COLORS)],
            'all_day' => ['boolean'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
        ]);

        CalendarEvent::create([
            ...$data,
            'user_id' => $request->user()->id,
            'team_id' => $request->user()->current_team_id,
            'color' => $data['color'] ?? 'accent',
            'source' => CalendarEvent::SOURCE_MANUAL,
        ]);

        return redirect()->back()->with('success', __('Event added to your calendar.'));
    }

    public function updateEvent(Request $request, CalendarEvent $event): RedirectResponse
    {
        abort_unless($event->user_id === $request->user()->id, 403);
        abort_if($event->source !== CalendarEvent::SOURCE_MANUAL, 422, 'Only manual events can be edited.');

        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'in:' . implode(',', CalendarEvent::COLORS)],
            'all_day' => ['boolean'],
            'starts_at' => ['sometimes', 'required', 'date'],
            'ends_at' => ['sometimes', 'required', 'date', 'after_or_equal:starts_at'],
        ]);

        $event->update($data);

        return redirect()->back()->with('success', __('Event updated.'));
    }

    public function destroyEvent(Request $request, CalendarEvent $event): RedirectResponse
    {
        abort_unless($event->user_id === $request->user()->id, 403);
        $event->delete();

        return redirect()->back()->with('success', __('Event deleted.'));
    }

    public function focusBlock(Request $request, Task $task): RedirectResponse
    {
        abort_unless($task->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'start' => ['required', 'date'],
            'minutes' => ['nullable', 'integer', 'min:15', 'max:240'],
        ]);

        $start = Carbon::parse($data['start']);
        $minutes = $data['minutes'] ?? 60;

        // Always create a native focus block (works without Google)
        CalendarEvent::create([
            'user_id' => $request->user()->id,
            'team_id' => $request->user()->current_team_id,
            'title' => '🎯 ' . $task->title,
            'color' => 'navy',
            'all_day' => false,
            'starts_at' => $start,
            'ends_at' => (clone $start)->addMinutes($minutes),
            'source' => CalendarEvent::SOURCE_FOCUS_BLOCK,
            'task_id' => $task->id,
        ]);

        $task->update(['focused_block_at' => $start]);

        // Additionally push to Google if connected
        $integration = $request->user()->integrations()
            ->where('provider', Integration::PROVIDER_GOOGLE)
            ->first();
        if ($integration) {
            $this->google->createFocusBlock($integration, $task->title, $start, $minutes);
        }

        return redirect()->back()->with('success', __('Focus block created on your calendar.'));
    }

    public function importWeakToInbox(Request $request): RedirectResponse
    {
        $user = $request->user();
        $integration = $user->integrations()
            ->where('provider', Integration::PROVIDER_GOOGLE)
            ->first();

        if (! $integration) {
            return redirect()->route('integrations.index')->with('error', 'Connect Google Calendar first.');
        }

        $events = $this->google->upcomingEvents($integration, 168);
        $created = 0;

        foreach ($events as $event) {
            if (! count($event['flags'])) continue;
            $title = 'Audit meeting: ' . $event['title'];
            if (Task::where('user_id', $user->id)->where('title', $title)->exists()) {
                continue;
            }
            Task::create([
                'user_id' => $user->id,
                'title' => $title,
                'description' => 'Flags: ' . implode(', ', $event['flags']),
                'status' => Task::STATUS_INBOX,
                'source' => 'calendar',
            ]);
            $created++;
        }

        return redirect()->route('tasks.index', ['status' => 'inbox'])
            ->with('success', "Imported {$created} meeting(s) to triage inbox.");
    }
}
