<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\CallLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle incoming webhooks from ElevenLabs.
     */
    public function handleElevenLabs(Request $request)
    {
        $rawBody = $request->getContent();
        $payload = $request->all();

        Log::info("=== ELEVENLABS WEBHOOK RECEIVED ===");
        Log::info("Headers: ", $request->headers->all());
        Log::info("Payload: ", $payload);
        Log::info("Raw Body: " . $rawBody);
        Log::info("===================================");

        // Try finding conversation_id from various common ElevenLabs payload structures
        $conversationId = $payload['conversation_id'] 
            ?? $payload['data']['conversation_id'] 
            ?? $payload['call_id'] 
            ?? null;

        if (!$conversationId) {
            Log::warning('Webhook received but no conversation_id found in payload.');
            return response()->json(['message' => 'Missing conversation_id', 'received_keys' => array_keys($payload)], 200);
        }

        // Search for existing CallLog by conversation_id
        $callLog = CallLog::where('elevenlabs_conversation_id', $conversationId)->first();
        $lead = null;

        if ($callLog) {
            $lead = $callLog->lead;
        } else {
            // Find lead by conversation_id directly or by active status
            $lead = Lead::where('elevenlabs_conversation_id', $conversationId)->first();

            if (!$lead) {
                Log::info("Lead not matched by conversation_id: {$conversationId}. Attempting match on latest in_progress lead.");
                $lead = Lead::whereIn('call_status', ['initiating', 'in_progress', 'ringing'])
                            ->orderBy('updated_at', 'desc')
                            ->first();
            }

            if ($lead) {
                // Find or create CallLog for this lead
                $callLog = CallLog::where('lead_id', $lead->id)
                            ->whereIn('call_status', ['initiating', 'in_progress', 'ringing', 'pending'])
                            ->orderBy('created_at', 'desc')
                            ->first();

                if (!$callLog) {
                    $callLog = CallLog::create([
                        'lead_id' => $lead->id,
                        'elevenlabs_conversation_id' => $conversationId,
                        'call_status' => 'in_progress'
                    ]);
                } else {
                    $callLog->update(['elevenlabs_conversation_id' => $conversationId]);
                }
            }
        }

        if ($lead && $callLog) {
            $status = $payload['status'] ?? $payload['event'] ?? 'completed';
            
            // Extract transcript & summary if provided
            $transcript = $payload['transcript'] ?? $payload['data']['transcript'] ?? null;
            $summary = $payload['summary'] ?? $payload['analysis']['transcript_summary'] ?? $payload['data']['summary'] ?? null;
            $recordingUrl = $payload['recording_url'] ?? $payload['data']['recording_url'] ?? null;

            $callSummaryText = null;
            if ($summary) {
                $callSummaryText = is_array($summary) ? json_encode($summary, JSON_PRETTY_PRINT) : $summary;
            } elseif ($transcript) {
                $callSummaryText = is_array($transcript) ? json_encode($transcript, JSON_PRETTY_PRINT) : $transcript;
            } else {
                $callSummaryText = json_encode($payload, JSON_PRETTY_PRINT);
            }

            // Update CallLog record with full backup of raw payload
            $callLog->update([
                'call_status' => 'completed',
                'call_summary' => $callSummaryText,
                'recording_url' => $recordingUrl,
                'raw_webhook_payload' => $payload ?: json_decode($rawBody, true)
            ]);

            // Sync latest details to Lead model
            $lead->update([
                'call_status' => 'completed',
                'elevenlabs_conversation_id' => $conversationId,
                'call_summary' => $callSummaryText,
                'recording_url' => $recordingUrl ?: $lead->recording_url
            ]);
        }

        return response()->json([
            'message' => 'Webhook received successfully',
            'conversation_id' => $conversationId
        ]);
    }
}
