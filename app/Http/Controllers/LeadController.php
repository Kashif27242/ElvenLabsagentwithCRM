<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Models\Lead;
use App\Services\LeadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LeadController extends Controller
{
    protected LeadService $leadService;

    public function __construct(LeadService $leadService)
    {
        $this->leadService = $leadService;
    }

    public function dashboard(): View
    {
        $stats = [
            'total_leads' => Lead::count(),
            'completed_calls' => Lead::where('call_status', 'completed')->count(),
            'in_progress_calls' => Lead::whereIn('call_status', ['initiating', 'in_progress', 'ringing'])->count(),
            'pending_calls' => Lead::where('call_status', 'pending')->count(),
        ];

        $recentLeads = Lead::orderBy('created_at', 'desc')->take(5)->get();

        return view('dashboard', compact('stats', 'recentLeads'));
    }

    public function index(): View
    {
        $leads = Lead::orderBy('created_at', 'desc')->get();
        return view('leads.index', compact('leads'));
    }

    public function create(): View
    {
        return view('leads.create');
    }

    public function store(StoreLeadRequest $request): RedirectResponse
    {
        $this->leadService->createLead($request->validated());

        return redirect()->route('leads.index')->with('success', 'Lead created successfully.');
    }

    public function show(Lead $lead): View
    {
        return view('leads.show', compact('lead'));
    }

    public function triggerCall(Lead $lead): RedirectResponse
    {
        $this->leadService->triggerCall($lead);
        
        return redirect()->back()->with('success', 'Outbound call initiated.');
    }

    /**
     * Poll ElevenLabs API directly to sync call status without requiring webhooks/ngrok.
     */
    public function checkStatus(Lead $lead, \App\Services\ElevenLabsService $elevenLabsService)
    {
        if (in_array($lead->call_status, ['completed', 'failed'])) {
            return response()->json(['call_status' => $lead->call_status, 'updated' => false]);
        }

        $convId = $lead->elevenlabs_conversation_id;

        if (!$convId) {
            // Find latest call log with conversation id
            $callLog = $lead->callLogs()->whereNotNull('elevenlabs_conversation_id')->first();
            if ($callLog) {
                $convId = $callLog->elevenlabs_conversation_id;
                $lead->update(['elevenlabs_conversation_id' => $convId]);
            }
        }

        if (!$convId) {
            return response()->json([
                'call_status' => $lead->call_status,
                'call_error_reason' => $lead->call_error_reason,
                'updated' => false
            ]);
        }

        $details = $elevenLabsService->getConversationDetails($convId);

        if ($details) {
            $status = strtolower($details['status'] ?? '');
            $startTime = $details['metadata']['start_time_unix_secs'] ?? null;
            $elapsedSeconds = $startTime ? (time() - $startTime) : 0;

            // Finished statuses in ElevenLabs API
            $isDone = in_array($status, ['done', 'completed', 'ended', 'finished']);
            $isFailed = in_array($status, ['failed', 'canceled', 'no_answer', 'busy', 'error']);
            
            // Timeout check: If status is still "initiated" or "in_progress" after > 45 seconds with no messages, mark as ended/unanswered
            $isTimedOut = ($status === 'initiated' || $status === 'in_progress') && ($elapsedSeconds > 45);

            if ($isDone || $isFailed || $isTimedOut) {
                $finalStatus = ($isFailed || ($isTimedOut && empty($details['transcript']))) ? 'failed' : 'completed';
                
                $aiSummary = $details['analysis']['transcript_summary'] 
                    ?? $details['analysis']['summary'] 
                    ?? $details['call_summary_title'] 
                    ?? null;

                if ($aiSummary) {
                    $summaryText = is_array($aiSummary) ? json_encode($aiSummary, JSON_PRETTY_PRINT) : $aiSummary;
                } else {
                    $transcriptMessages = [];
                    if (!empty($details['transcript'])) {
                        foreach ($details['transcript'] as $turn) {
                            $role = ($turn['role'] ?? 'speaker') === 'agent' ? 'AI Agent' : 'Lead';
                            $msg = trim($turn['message'] ?? '', '"');
                            $transcriptMessages[] = "{$role}: {$msg}";
                        }
                    }

                    $summaryText = !empty($transcriptMessages) 
                        ? implode("\n", $transcriptMessages) 
                        : ($finalStatus === 'failed' ? 'Call declined, unanswered, or ended by recipient.' : 'Call finished.');
                }

                $recordingUrl = route('conversations.audio', $convId);
                $errorReason = ($finalStatus === 'failed') ? ($details['error'] ?? 'Call declined or unanswered by lead.') : null;

                // Update Lead
                $lead->update([
                    'call_status' => $finalStatus,
                    'call_summary' => $summaryText,
                    'recording_url' => $recordingUrl,
                    'call_error_reason' => $errorReason
                ]);

                // Update CallLog
                $callLog = \App\Models\CallLog::where('elevenlabs_conversation_id', $convId)->first();
                if ($callLog) {
                    $callLog->update([
                        'call_status' => $finalStatus,
                        'call_summary' => $summaryText,
                        'recording_url' => $recordingUrl,
                        'call_error_reason' => $errorReason,
                        'raw_webhook_payload' => $details
                    ]);
                }

                return response()->json([
                    'call_status' => $finalStatus,
                    'updated' => true,
                    'summary' => $summaryText
                ]);
            }
        }

        return response()->json([
            'call_status' => $lead->call_status,
            'updated' => false
        ]);
    }
}
