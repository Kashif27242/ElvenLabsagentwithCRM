@extends('layouts.app')

@section('header_title', 'All Conversations')

@section('content')
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h3 class="card-title">ElevenLabs Call Conversations Directory</h3>
            <span style="font-size: 0.85rem; color: var(--text-muted);">Fetched directly from ElevenLabs Conversational AI API</span>
        </div>
    </div>

    <div class="table-responsive" style="margin-top: 1rem;">
        <table class="modern-table">
            <thead>
                <tr>
                    <th>Conversation ID</th>
                    <th>Matched Lead / Contact</th>
                    <th>Topic / Summary Title</th>
                    <th>Duration</th>
                    <th>Status</th>
                    <th>Date & Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($conversations as $conv)
                @php
                    $convId = $conv['conversation_id'];
                    $matchedLog = $callLogs[$convId] ?? null;
                    $lead = $matchedLog ? $matchedLog->lead : null;
                    $duration = isset($conv['call_duration_secs']) ? sprintf('%dm %ds', floor($conv['call_duration_secs'] / 60), $conv['call_duration_secs'] % 60) : 'N/A';
                    $startTime = isset($conv['start_time_unix_secs']) ? \Carbon\Carbon::createFromTimestamp($conv['start_time_unix_secs'])->format('M d, Y H:i') : 'N/A';
                @endphp
                <tr>
                    <td>
                        <code style="font-size: 0.82rem; background: #f1f5f9; padding: 0.2rem 0.5rem; border-radius: 4px; color: #334155;">
                            {{ $convId }}
                        </code>
                    </td>
                    <td>
                        @if($lead)
                            <a href="{{ route('leads.show', $lead) }}" style="font-weight: 600; color: var(--primary); text-decoration: none;">
                                {{ $lead->first_name }} {{ $lead->last_name }}
                            </a>
                            <div style="font-size: 0.78rem; color: var(--text-muted);">{{ $lead->phone_number }}</div>
                        @else
                            <span style="color: var(--text-muted); font-size: 0.85rem;">Unmatched Call</span>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight: 500; font-size: 0.9rem;">
                            {{ $conv['call_summary_title'] ?? 'Voice Call Session' }}
                        </div>
                    </td>
                    <td><span style="font-size: 0.85rem; font-weight: 500;">{{ $duration }}</span></td>
                    <td>
                        <span class="badge badge-{{ strtolower($conv['status'] ?? 'completed') }}">
                            {{ ucfirst($conv['status'] ?? 'done') }}
                        </span>
                    </td>
                    <td style="font-size: 0.85rem; color: var(--text-muted);">{{ $startTime }}</td>
                    <td>
                        <a href="{{ route('conversations.show', $convId) }}" class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 0.3rem;">
                            <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            View Conversation
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 3rem;">
                        No conversations found in ElevenLabs account yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
