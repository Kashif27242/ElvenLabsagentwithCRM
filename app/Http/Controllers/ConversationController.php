<?php

namespace App\Http\Controllers;

use App\Models\CallLog;
use App\Services\ElevenLabsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class ConversationController extends Controller
{
    protected ElevenLabsService $elevenLabsService;

    public function __construct(ElevenLabsService $elevenLabsService)
    {
        $this->elevenLabsService = $elevenLabsService;
    }

    /**
     * Display a listing of all conversations.
     */
    public function index(): View
    {
        $apiData = $this->elevenLabsService->getConversations(50);
        $conversations = $apiData['conversations'] ?? [];

        // Collect conversation IDs and match with database CallLogs & Leads
        $conversationIds = array_column($conversations, 'conversation_id');
        $callLogs = CallLog::with('lead')
            ->whereIn('elevenlabs_conversation_id', $conversationIds)
            ->get()
            ->keyBy('elevenlabs_conversation_id');

        return view('conversations.index', compact('conversations', 'callLogs'));
    }

    /**
     * Display full details and transcript of a single conversation.
     */
    public function show(string $conversationId): View
    {
        $details = $this->elevenLabsService->getConversationDetails($conversationId);
        $callLog = CallLog::with('lead')
            ->where('elevenlabs_conversation_id', $conversationId)
            ->first();

        return view('conversations.show', compact('details', 'conversationId', 'callLog'));
    }

    /**
     * Proxy audio recording directly from ElevenLabs API.
     */
    public function audio(string $conversationId)
    {
        $apiKey = env('Eleven_labs_api', '');

        $response = Http::withHeaders([
            'xi-api-key' => $apiKey,
        ])->get("https://api.elevenlabs.io/v1/convai/conversations/{$conversationId}/audio");

        if ($response->successful()) {
            return response($response->body(), 200, [
                'Content-Type' => 'audio/mpeg',
                'Content-Disposition' => "inline; filename=\"conversation_{$conversationId}.mp3\"",
            ]);
        }

        abort(404, 'Audio recording not found for this conversation.');
    }
}
