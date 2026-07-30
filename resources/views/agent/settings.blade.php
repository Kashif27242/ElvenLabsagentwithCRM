@extends('layouts.app')

@section('header_title', 'ElevenLabs Agent Settings')

@section('content')
<div style="max-width: 850px; margin: 0 auto;">

    @if (session('error'))
        <div style="background-color: var(--danger-bg); color: var(--danger); border: 1px solid rgba(239, 68, 68, 0.3); padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1.5rem;">
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 1rem; margin-bottom: 1.5rem;">
            <div>
                <h3 class="card-title">3knot Voice Agent Configuration</h3>
                <span style="font-size: 0.85rem; color: var(--text-muted);">Agent ID: <code>{{ $agentId ?: 'Not Configured' }}</code></span>
            </div>
            <span class="badge badge-completed">ElevenLabs API Connected</span>
        </div>

        @if($agent)
            <form action="{{ route('agent.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label">Agent Name</label>
                    <input type="text" name="name" class="form-control" value="{{ $agent['name'] ?? '' }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">First Message (Greeting)</label>
                    <input type="text" name="first_message" class="form-control" value="{{ $agent['conversation_config']['agent']['first_message'] ?? '' }}" placeholder="Hello! This is 3knot Digital Voice calling...">
                    <small style="color: var(--text-muted); display: block; margin-top: 0.35rem;">The first sentence the AI voice agent says when the lead answers.</small>
                </div>

                <div class="form-group">
                    <label class="form-label">System Prompt / Agent Persona</label>
                    <textarea name="prompt" class="form-control" rows="8" placeholder="Define the AI persona, rules, objective, and instructions...">{{ $agent['conversation_config']['agent']['prompt']['prompt'] ?? '' }}</textarea>
                    <small style="color: var(--text-muted); display: block; margin-top: 0.35rem;">Instructions that dictate how the AI agent behaves, speaks, and responds.</small>
                </div>

                <div style="margin-top: 2rem; display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem;">
                        <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Save & Update Agent via API
                    </button>
                </div>
            </form>
        @else
            <div style="text-align: center; color: var(--text-muted); padding: 3rem 1rem;">
                <svg style="width: 48px; height: 48px; margin: 0 auto 1rem; color: var(--warning);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <h4 style="font-size: 1.1rem; color: var(--text-heading); margin-bottom: 0.5rem;">Agent Configuration Unavailable</h4>
                <p>Could not fetch agent details from ElevenLabs. Please verify that <code>Eleven_labs_api</code> and <code>Agent_ID</code> in your <code>.env</code> file are correct.</p>
            </div>
        @endif
    </div>
</div>
@endsection
