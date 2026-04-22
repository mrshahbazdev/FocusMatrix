<?php

namespace App\Http\Controllers;

use App\Models\Integration;
use App\Models\Task;
use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class CalendarController extends Controller
{
    public function __construct(private GoogleCalendarService $google)
    {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $integration = $user->integrations()
            ->where('provider', Integration::PROVIDER_GOOGLE)
            ->first();

        $events = $integration ? $this->google->upcomingEvents($integration, 96) : [];

        $weak = collect($events)->filter(fn ($e) => count($e['flags']) > 0)->values()->all();

        return Inertia::render('Calendar/Index', [
            'connected' => (bool) $integration,
            'account_email' => $integration?->account_email,
            'events' => $events,
            'weak_meetings' => $weak,
        ]);
    }

    public function focusBlock(Request $request, Task $task)
    {
        abort_unless($task->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'start' => ['required', 'date'],
            'minutes' => ['nullable', 'integer', 'min:15', 'max:240'],
        ]);

        $integration = $request->user()->integrations()
            ->where('provider', Integration::PROVIDER_GOOGLE)
            ->first();

        if (! $integration) {
            return redirect()->back()->with('error', 'Connect Google Calendar first.');
        }

        $link = $this->google->createFocusBlock(
            $integration,
            $task->title,
            Carbon::parse($data['start']),
            $data['minutes'] ?? 60,
        );

        if (! $link) {
            return redirect()->back()->with('error', 'Calendar event could not be created.');
        }

        $task->update(['focused_block_at' => Carbon::parse($data['start'])]);

        return redirect()->back()->with('success', 'Focus block created on your calendar.');
    }

    public function importWeakToInbox(Request $request)
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
