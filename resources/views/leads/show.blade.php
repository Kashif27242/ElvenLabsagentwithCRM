@extends('layouts.app')

@section('header_title', 'Lead Details')

@section('content')
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
    <!-- Lead Profile Card -->
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
            <div>
                <h3 class="card-title">{{ $lead->first_name }} {{ $lead->last_name }}</h3>
                <span style="font-size: 0.85rem; color: var(--text-muted);">Created on {{ $lead->created_at->format('M d, Y \a\t H:i') }}</span>
            </div>
            <span class="badge badge-{{ strtolower($lead->call_status) }}">{{ str_replace('_', ' ', $lead->call_status) }}</span>
        </div>

        <div style="margin-top: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
            <div>
                <span class="form-label" style="margin-bottom: 0.2rem;">Phone Number</span>
                <strong style="font-size: 1.1rem; color: var(--text-heading);">{{ $lead->phone_number }}</strong>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <span class="form-label" style="margin-bottom: 0.2rem;">Email</span>
                    <div>{{ $lead->email ?: 'N/A' }}</div>
                </div>

                <div>
                    <span class="form-label" style="margin-bottom: 0.2rem;">Company</span>
                    <div>{{ $lead->company ?: 'N/A' }}</div>
                </div>
            </div>

            <div>
                <span class="form-label" style="margin-bottom: 0.2rem;">Dispatch Strategy</span>
                <div style="font-weight: 600;">
                    {{ ucfirst($lead->call_type) }} Call 
                    @if($lead->call_type === 'auto' && $lead->call_delay_minutes > 0)
                        (Scheduled {{ $lead->call_delay_minutes }}m after creation)
                    @endif
                </div>
            </div>

            <div>
                <span class="form-label" style="margin-bottom: 0.2rem;">Agent Context</span>
                <div style="background-color: #f8fafc; padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-color); font-size: 0.9rem;">
                    {{ $lead->context ?: 'No additional context provided for this lead.' }}
                </div>
            </div>
        </div>

        @if(!in_array($lead->call_status, ['initiating', 'in_progress', 'ringing']))
            <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                <form action="{{ route('leads.trigger-call', $lead) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                        <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        @if($lead->call_status === 'completed')
                            Call Lead Again (Redial)
                        @elseif($lead->call_status === 'failed')
                            Retry Outbound Voice Call
                        @else
                            Initiate 3knot Digital Voice Call Now
                        @endif
                    </button>
                </form>
            </div>
        @endif
    </div>

    <!-- AI Agent Summary & Call Analytics Panel -->
    <div class="card" x-data="{ activeTab: 'timeline' }">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title">Voice Call Analytics</h3>
            <span class="badge" style="background: #f1f5f9; color: #475569;">{{ $lead->callLogs->count() }} {{ Str::plural('Attempt', $lead->callLogs->count()) }}</span>
        </div>

        <!-- Tab Navigation -->
        <div class="tab-nav">
            <button type="button" class="tab-btn" :class="{ 'active': activeTab === 'timeline' }" @click="activeTab = 'timeline'">
                <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Call History Timeline
            </button>
            <button type="button" class="tab-btn" :class="{ 'active': activeTab === 'webhooks' }" @click="activeTab = 'webhooks'">
                <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                Developer Webhook Logs
            </button>
        </div>

        @if(in_array($lead->call_status, ['initiating', 'in_progress', 'ringing']))
            <div style="background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; text-align: center;">
                <div class="status-dot" style="margin: 0 auto 0.5rem; width: 12px; height: 12px;"></div>
                <strong>Call Currently In Progress...</strong>
                <div style="font-size: 0.85rem; margin-top: 0.25rem;">Syncing live status directly with ElevenLabs API...</div>
                <script>
                    (function startPolling() {
                        const checkInterval = setInterval(function() {
                            fetch("{{ route('leads.check-status', $lead) }}")
                                .then(res => res.json())
                                .then(data => {
                                    if (data.call_status === 'completed' || data.updated) {
                                        clearInterval(checkInterval);
                                        window.location.reload();
                                    }
                                })
                                .catch(err => console.error('Status check error:', err));
                        }, 3000);
                    })();
                </script>
            </div>
        @endif

        <!-- TAB 1: CALL HISTORY TIMELINE -->
        <div x-show="activeTab === 'timeline'" class="timeline-container">
            @forelse($lead->callLogs as $index => $log)
                @php
                    $isCompleted = $log->call_status === 'completed';
                    $isFailed = $log->call_status === 'failed';
                    $attemptNum = $lead->callLogs->count() - $index;
                @endphp
                <div class="timeline-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-weight: 800; font-size: 0.95rem; color: var(--text-heading);">Attempt #{{ $attemptNum }}</span>
                            <span style="font-size: 0.78rem; color: var(--text-muted);">• {{ $log->created_at->format('M d, Y \a\t H:i') }}</span>
                        </div>
                        <span class="badge badge-{{ strtolower($log->call_status) }}">
                            {{ str_replace('_', ' ', $log->call_status) }}
                        </span>
                    </div>

                    @if($isCompleted)
                        <!-- AI Summary & Transcript -->
                        <div style="background-color: #f8fafc; border: 1px solid var(--border-color); padding: 0.85rem 1rem; border-radius: var(--radius-md); font-size: 0.88rem; line-height: 1.5; color: var(--text-body); white-space: pre-wrap; margin-bottom: 0.75rem;">
                            <strong style="display: block; color: var(--primary); font-size: 0.8rem; margin-bottom: 0.3rem;">AI CALL SUMMARY & TRANSCRIPT</strong>
                            {{ $log->call_summary ?: 'Call completed successfully.' }}
                        </div>

                        <!-- Audio Recording (Only if available) -->
                        @if($log->recording_url)
                            <div style="margin-bottom: 0.75rem;">
                                <span class="form-label" style="font-size: 0.78rem; margin-bottom: 0.25rem;">Call Audio Playback</span>
                                <audio controls style="width: 100%; height: 36px; margin-top: 0;">
                                    <source src="{{ $log->recording_url }}" type="audio/mpeg">
                                    Audio playback not supported.
                                </audio>
                            </div>
                        @endif

                        @if($log->elevenlabs_conversation_id)
                            <div style="margin-top: 0.5rem;">
                                <a href="{{ route('conversations.show', $log->elevenlabs_conversation_id) }}" class="btn btn-secondary" style="padding: 0.35rem 0.75rem; font-size: 0.78rem; display: inline-flex; align-items: center; gap: 0.3rem;">
                                    <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    View Full Turn-by-Turn Transcript
                                </a>
                            </div>
                        @endif

                    @elseif($isFailed)
                        <!-- Clean Failure Status -->
                        <div style="background-color: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.85rem 1rem; border-radius: var(--radius-md); font-size: 0.88rem;">
                            <strong>Outcome:</strong> {{ $log->call_summary ?: ($log->call_error_reason ?: 'Call declined, unanswered, or ended by recipient.') }}
                        </div>
                    @else
                        <div style="font-size: 0.85rem; color: var(--text-muted);">
                            Call session initiated. Awaiting response...
                        </div>
                    @endif
                </div>
            @empty
                <div style="text-align: center; color: var(--text-muted); padding: 3rem 1rem;">
                    <strong>No Call History Found</strong>
                    <div style="font-size: 0.85rem; margin-top: 0.5rem;">Click "Initiate 3knot Digital Voice Call Now" to start the first call attempt.</div>
                </div>
            @endforelse
        </div>

        <!-- TAB 2: DEVELOPER WEBHOOK LOGS -->
        <div x-show="activeTab === 'webhooks'" style="display: flex; flex-direction: column; gap: 1.25rem;">
            @forelse($lead->callLogs as $index => $log)
                <div style="border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1rem; background: #0f172a; color: #f8fafc;">
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; padding-bottom: 0.5rem; margin-bottom: 0.75rem;">
                        <span style="font-size: 0.85rem; font-weight: 700; color: #38bdf8;">Attempt #{{ $lead->callLogs->count() - $index }} — Conversation Payload</span>
                        <code style="font-size: 0.75rem; color: #94a3b8;">{{ $log->elevenlabs_conversation_id ?: 'No Conv ID' }}</code>
                    </div>

                    @if($log->raw_webhook_payload)
                        <pre style="margin: 0; font-size: 0.78rem; color: #a5f3fc; overflow-x: auto; font-family: monospace; max-height: 250px;">{{ json_encode($log->raw_webhook_payload, JSON_PRETTY_PRINT) }}</pre>
                    @else
                        <div style="font-size: 0.8rem; color: #94a3b8;">No raw webhook payload stored for this attempt yet.</div>
                    @endif
                </div>
            @empty
                <div style="text-align: center; color: var(--text-muted); padding: 3rem 1rem;">
                    No webhook logs recorded yet.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
