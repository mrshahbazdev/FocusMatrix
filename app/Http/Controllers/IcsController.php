<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class IcsController extends Controller
{
    /**
     * Public ICS calendar feed. Token-based so users can subscribe from
     * Outlook / Apple Calendar / Google Calendar without logging in.
     * URL: /calendar/ics/{token}
     */
    public function feed(string $token): Response
    {
        $user = User::where('ics_token', $token)->firstOrFail();

        $tasks = Task::where('user_id', $user->id)
            ->where('status', Task::STATUS_KEEP)
            ->whereNotNull('due_at')
            ->orderBy('due_at')
            ->limit(200)
            ->get();

        $now = now();
        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//FocusMatrix//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:FocusMatrix · ' . $user->name,
            'X-WR-TIMEZONE:UTC',
        ];

        foreach ($tasks as $task) {
            $start = $task->due_at ?? $now;
            $end = (clone $start)->addMinutes(60);
            $uid = 'task-' . $task->id . '@focusmatrix.app';
            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:' . $uid;
            $lines[] = 'DTSTAMP:' . $now->format('Ymd\THis\Z');
            $lines[] = 'DTSTART:' . $start->format('Ymd\THis\Z');
            $lines[] = 'DTEND:' . $end->format('Ymd\THis\Z');
            $lines[] = 'SUMMARY:' . $this->escape('[Focus] ' . $task->title);
            $lines[] = 'DESCRIPTION:' . $this->escape(
                "Only-You task · " . ($task->only_you_category ? Str::of($task->only_you_category)->replace('_', ' ')->title() . ' · ' : '')
                . ($task->description ?? '')
            );
            $lines[] = 'CATEGORIES:FocusMatrix,Keep';
            $lines[] = 'STATUS:CONFIRMED';
            $lines[] = 'TRANSP:OPAQUE';
            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';
        $body = implode("\r\n", $lines) . "\r\n";

        return response($body, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename="focusmatrix.ics"',
            'Cache-Control' => 'max-age=300, public',
        ]);
    }

    private function escape(string $s): string
    {
        return str_replace(
            ["\\", ",", ";", "\n", "\r"],
            ["\\\\", "\\,", "\\;", "\\n", ""],
            Str::limit($s, 900, '')
        );
    }
}
