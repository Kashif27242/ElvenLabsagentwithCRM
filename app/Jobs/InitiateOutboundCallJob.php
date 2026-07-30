<?php

namespace App\Jobs;

use App\Models\Lead;
use App\Services\ElevenLabsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

use App\Models\CallLog;

class InitiateOutboundCallJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public Lead $lead;

    /**
     * Create a new job instance.
     */
    public function __construct(Lead $lead)
    {
        $this->lead = $lead;
    }

    /**
     * Execute the job.
     */
    public function handle(ElevenLabsService $elevenLabsService): void
    {
        if (in_array($this->lead->call_status, ['initiating', 'in_progress', 'ringing'])) {
            return;
        }

        // Using Agent_ID from .env as configured by the user
        $agentId = env('Agent_ID', 'default_agent_id');

        $dynamicVariables = [
            'first_name' => $this->lead->first_name,
            'company' => $this->lead->company ?? '',
            'context' => $this->lead->context ?? ''
        ];

        // Create new CallLog record for this call attempt
        $callLog = CallLog::create([
            'lead_id' => $this->lead->id,
            'call_status' => 'initiating'
        ]);

        $this->lead->update(['call_status' => 'initiating']);

        $response = $elevenLabsService->triggerOutboundCall($this->lead->phone_number, $agentId, $dynamicVariables);

        if (!empty($response['success'])) {
            $convId = $response['conversation_id'] ?? null;

            $callLog->update([
                'elevenlabs_conversation_id' => $convId,
                'call_status' => 'in_progress',
            ]);

            $this->lead->update([
                'elevenlabs_conversation_id' => $convId,
                'call_status' => 'in_progress',
                'call_error_reason' => null
            ]);
        } else {
            $errorMsg = $response['error'] ?? 'Unknown error occurred while initiating call.';

            $callLog->update([
                'call_status' => 'failed',
                'call_error_reason' => $errorMsg
            ]);

            $this->lead->update([
                'call_status' => 'failed',
                'call_error_reason' => $errorMsg
            ]);

            Log::error("Failed to initiate call for Lead ID: {$this->lead->id}. Reason: {$errorMsg}");
        }
    }
}
