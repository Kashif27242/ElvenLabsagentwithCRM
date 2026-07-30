@extends('layouts.app')

@section('header_title', 'Conversation Details')

@section('content')
<div style="margin-bottom: 1rem;">
    <a href="{{ route('conversations.index') }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.4rem 0.8rem; font-size: 0.85rem;">
        <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back to All Conversations
    </a>
</div>

<div style="display: grid; grid-template-columns: 320px 1fr; gap: 1.5rem;">
    <!-- Metadata & Audio Sidebar Card -->
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        <div class="card">
            <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
                <h3 class="card-title" style="font-size: 1.1rem;">Call Metadata</h3>
            </div>

            <div style="margin-top: 1rem; display: flex; flex-direction: column; gap: 1rem; font-size: 0.9rem;">
                <div>
                    <span class="form-label" style="font-size: 0.78rem; margin-bottom: 0.2rem;">Conversation ID</span>
                    <code style="font-size: 0.8rem; word-break: break-all; background: #f1f5f9; padding: 0.3rem 0.5rem; border-radius: 4px; display: block; color: #334155;">
                        {{ $conversationId }}
                    </code>
                </div>

                <div>
                    <span class="form-label" style="font-size: 0.78rem; margin-bottom: 0.2rem;">Status</span>
                    <span class="badge badge-{{ strtolower($details['status'] ?? 'done') }}">
                        {{ ucfirst($details['status'] ?? 'done') }}
                    </span>
                </div>

                @if(isset($details['agent_name']))
                <div>
                    <span class="form-label" style="font-size: 0.78rem; margin-bottom: 0.2rem;">Agent Name</span>
                    <strong>{{ $details['agent_name'] }}</strong>
                </div>
                @endif

                @if(isset($details['metadata']['call_duration_secs']) || isset($details['transcript']))
                @php
                    $secs = $details['metadata']['call_duration_secs'] ?? count($details['transcript']) * 5;
                @endphp
                <div>
                    <span class="form-label" style="font-size: 0.78rem; margin-bottom: 0.2rem;">Call Duration</span>
                    <strong>{{ sprintf('%dm %ds', floor($secs / 60), $secs % 60) }}</strong>
                </div>
                @endif

                @if(isset($details['metadata']['start_time_unix_secs']))
                <div>
                    <span class="form-label" style="font-size: 0.78rem; margin-bottom: 0.2rem;">Start Time</span>
                    <div>{{ \Carbon\Carbon::createFromTimestamp($details['metadata']['start_time_unix_secs'])->format('M d, Y \a\t H:i:s') }}</div>
                </div>
                @endif

                @if(isset($details['termination_reason']))
                <div>
                    <span class="form-label" style="font-size: 0.78rem; margin-bottom: 0.2rem;">Termination Reason</span>
                    <div style="font-size: 0.85rem; color: #64748b;">{{ $details['termination_reason'] }}</div>
                </div>
                @endif
            </div>

            <!-- Audio Recording -->
            <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                <span class="form-label" style="font-size: 0.8rem; margin-bottom: 0.5rem;">Call Audio Recording</span>
                <audio controls style="width: 100%;">
                    <source src="{{ route('conversations.audio', $conversationId) }}" type="audio/mpeg">
                    Your browser does not support audio playback.
                </audio>
            </div>
        </div>

        @if($callLog && $callLog->lead)
        <!-- Matched Lead Card -->
        <div class="card" style="background: #f8fafc; border: 1px solid var(--primary-border);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                <span style="font-weight: 600; font-size: 0.85rem; color: var(--primary);">Matched Lead</span>
                <a href="{{ route('leads.show', $callLog->lead) }}" style="font-size: 0.8rem; color: var(--primary); text-decoration: underline;">View Profile</a>
            </div>
            <strong style="font-size: 1rem; color: var(--text-heading);">{{ $callLog->lead->first_name }} {{ $callLog->lead->last_name }}</strong>
            <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.2rem;">{{ $callLog->lead->phone_number }}</div>
            @if($callLog->lead->company)
                <div style="font-size: 0.8rem; color: #64748b; margin-top: 0.4rem;">Company: {{ $callLog->lead->company }}</div>
            @endif
        </div>
        @endif
    </div>

    <!-- Transcript Chat Bubbles View -->
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title">Full Call Transcript</h3>
            <span style="font-size: 0.85rem; color: var(--text-muted);">
                {{ count($details['transcript'] ?? []) }} Message Turns
            </span>
        </div>

        <div style="margin-top: 1.5rem; display: flex; flex-direction: column; gap: 1.25rem;">
            @forelse($details['transcript'] ?? [] as $turn)
                @php
                    $isAgent = ($turn['role'] ?? '') === 'agent';
                @endphp
                <div style="display: flex; flex-direction: column; align-items: {{ $isAgent ? 'flex-start' : 'flex-end' }};">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem; font-size: 0.78rem; color: var(--text-muted);">
                        <strong>{{ $isAgent ? '🤖 3knot AI Voice Agent' : '👤 Lead / User' }}</strong>
                        @if(isset($turn['time_in_call_secs']))
                            <span>• {{ sprintf('%02d:%02d', floor($turn['time_in_call_secs'] / 60), $turn['time_in_call_secs'] % 60) }}</span>
                        @endif
                    </div>
                    <div style="max-width: 80%; padding: 0.9rem 1.1rem; border-radius: 12px; font-size: 0.92rem; line-height: 1.55; {{ $isAgent ? 'background-color: #f1f5f9; color: #0f172a; border-bottom-left-radius: 2px;' : 'background-color: var(--primary); color: #ffffff; border-bottom-right-radius: 2px;' }}">
                        {{ trim($turn['message'] ?? '', '"') }}
                    </div>
                </div>
            @empty
                <div style="text-align: center; color: var(--text-muted); padding: 3rem;">
                    No transcript entries available for this conversation.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
