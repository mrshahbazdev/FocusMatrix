<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class VoiceController extends Controller
{
    /**
     * Transcribe an uploaded audio clip using the user's OpenAI Whisper API key (BYOK).
     * Optionally create a task from the transcription.
     *
     * Only OpenAI is supported for transcription — Gemini/Anthropic don't expose Whisper.
     */
    public function transcribe(Request $request): JsonResponse
    {
        $request->validate([
            'audio' => ['required', 'file', 'max:15360', 'mimetypes:audio/webm,audio/ogg,audio/mpeg,audio/mp4,audio/wav,audio/x-wav,audio/x-m4a,video/webm'],
            'create_task' => ['nullable', 'boolean'],
            'language' => ['nullable', 'string', 'max:5'],
        ]);

        $user = $request->user();
        $setting = $user->aiSetting;

        if (! $setting || ! $setting->hasKey() || ! $setting->enabled) {
            return response()->json([
                'ok' => false,
                'message' => __('Voice capture requires an OpenAI API key. Add one in Settings → AI.'),
            ], 200);
        }
        if ($setting->provider !== 'openai') {
            return response()->json([
                'ok' => false,
                'message' => __('Voice capture currently only supports the OpenAI provider. Switch provider in Settings → AI to use Whisper.'),
            ], 200);
        }
        if ($setting->remainingQuota() <= 0) {
            return response()->json([
                'ok' => false,
                'message' => __('Monthly AI quota reached. Raise the limit in Settings → AI.'),
            ], 200);
        }

        $file = $request->file('audio');
        $apiKey = $setting->getApiKey();

        try {
            $response = Http::withToken($apiKey)
                ->timeout(60)
                ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName() ?: 'clip.webm', ['Content-Type' => $file->getMimeType() ?: 'audio/webm'])
                ->asMultipart()
                ->post('https://api.openai.com/v1/audio/transcriptions', array_filter([
                    'model' => 'whisper-1',
                    'language' => $request->input('language') ?: ($user->locale ?? null),
                    'response_format' => 'json',
                    'temperature' => '0',
                ]));
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 200);
        }

        if (! $response->successful()) {
            $msg = $response->json('error.message') ?? ('HTTP ' . $response->status());
            return response()->json(['ok' => false, 'message' => $msg], 200);
        }

        $setting->registerCall();

        $text = trim((string) $response->json('text'));
        if ($text === '') {
            return response()->json(['ok' => false, 'message' => __('No speech detected.')], 200);
        }

        $task = null;
        if ($request->boolean('create_task')) {
            $title = mb_strimwidth($text, 0, 140, '…');
            $task = Task::create([
                'user_id' => $user->id,
                'team_id' => $user->current_team_id,
                'title' => $title,
                'description' => $text === $title ? null : $text,
                'status' => Task::STATUS_INBOX,
                'source' => 'voice',
            ]);
        }

        return response()->json([
            'ok' => true,
            'text' => $text,
            'task' => $task ? ['id' => $task->id, 'title' => $task->title] : null,
        ]);
    }
}
