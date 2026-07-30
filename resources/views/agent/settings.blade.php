@extends('layouts.app')

@section('header_title', 'ElevenLabs Agent Settings')

@section('content')
<div style="max-width: 950px; margin: 0 auto;">

    @if (session('error'))
        <div style="background-color: var(--danger-bg); color: var(--danger); border: 1px solid rgba(239, 68, 68, 0.3); padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1.5rem;">
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 1rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h3 class="card-title">3knot Voice Agent Configuration</h3>
                <span style="font-size: 0.85rem; color: var(--text-muted);">Agent ID: <code>{{ $agentId ?: 'Not Configured' }}</code></span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                @if($agent && isset($agent['conversation_config']['agent']['prompt']['llm']))
                    <span class="badge" style="background: var(--primary-bg); color: var(--primary); font-family: monospace; font-size: 0.8rem;">
                        LLM: {{ $agent['conversation_config']['agent']['prompt']['llm'] }}
                    </span>
                @endif
                <span class="badge badge-completed">ElevenLabs API Connected</span>
            </div>
        </div>

        @if($agent)
            @php
                $currentLlm = $agent['conversation_config']['agent']['prompt']['llm'] ?? 'gpt-5.6-sol';
                $currentVoiceId = $agent['conversation_config']['tts']['voice_id'] ?? '';
                $currentTtsModel = $agent['conversation_config']['tts']['model_id'] ?? 'eleven_v3_conversational';
                $currentTemperature = $agent['conversation_config']['agent']['prompt']['temperature'] ?? 0.55;
            @endphp

            <form action="{{ route('agent.update') }}" method="POST">
                @csrf
                @method('PUT')

                <!-- 1. General Settings -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Agent Name</label>
                        <input type="text" name="name" class="form-control" value="{{ $agent['name'] ?? '' }}" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">AI Language Model (LLM)</label>
                        <select name="llm" class="form-control" style="font-family: inherit;">
                            <option value="gpt-5.6-sol" {{ $currentLlm === 'gpt-5.6-sol' ? 'selected' : '' }}>GPT-5.6 Sol (Default Configured)</option>
                            <option value="gpt-4o" {{ $currentLlm === 'gpt-4o' ? 'selected' : '' }}>OpenAI GPT-4o (High Reasoning & Intelligence)</option>
                            <option value="gpt-4o-mini" {{ $currentLlm === 'gpt-4o-mini' ? 'selected' : '' }}>OpenAI GPT-4o Mini (Fast & Cost Efficient)</option>
                            <option value="claude-3-5-sonnet" {{ $currentLlm === 'claude-3-5-sonnet' ? 'selected' : '' }}>Anthropic Claude 3.5 Sonnet (Natural Conversational Tone)</option>
                            <option value="gemini-2.0-flash" {{ $currentLlm === 'gemini-2.0-flash' ? 'selected' : '' }}>Google Gemini 2.0 Flash (Ultra-low Latency)</option>
                            <option value="gemini-1.5-flash" {{ $currentLlm === 'gemini-1.5-flash' ? 'selected' : '' }}>Google Gemini 1.5 Flash (Fast Speed)</option>
                            <option value="gemini-1.5-pro" {{ $currentLlm === 'gemini-1.5-pro' ? 'selected' : '' }}>Google Gemini 1.5 Pro (Deep Context Window)</option>
                            @if(!in_array($currentLlm, ['gpt-5.6-sol', 'gpt-4o', 'gpt-4o-mini', 'claude-3-5-sonnet', 'gemini-2.0-flash', 'gemini-1.5-flash', 'gemini-1.5-pro']))
                                <option value="{{ $currentLlm }}" selected>Custom: {{ $currentLlm }}</option>
                            @endif
                        </select>
                        <small style="color: var(--text-muted); display: block; margin-top: 0.35rem;">Switches the backend intelligence model for live agent phone calls.</small>
                    </div>
                </div>

                <!-- 2. Voice & Speech Synthesis Settings -->
                <div style="background-color: #f8fafc; border: 1px solid var(--border-color); padding: 1.25rem; border-radius: var(--radius-md); margin-bottom: 1.5rem;">
                    <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--text-heading); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        <svg style="width: 18px; height: 18px; color: var(--primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
                        Voice Synthesis & Speech Engine
                    </h4>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Voice ID</label>
                            <input type="text" name="voice_id" class="form-control" value="{{ $currentVoiceId }}" placeholder="e.g. T720RsqorTx4ZZWohrNN">
                            <small style="color: var(--text-muted); display: block; margin-top: 0.25rem;">ElevenLabs Voice ID for output audio.</small>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">TTS Engine Model</label>
                            <select name="tts_model_id" class="form-control">
                                <option value="eleven_v3_conversational" {{ $currentTtsModel === 'eleven_v3_conversational' ? 'selected' : '' }}>eleven_v3_conversational (Recommended)</option>
                                <option value="eleven_turbo_v2_5" {{ $currentTtsModel === 'eleven_turbo_v2_5' ? 'selected' : '' }}>eleven_turbo_v2_5 (Low Latency)</option>
                                <option value="eleven_multilingual_v2" {{ $currentTtsModel === 'eleven_multilingual_v2' ? 'selected' : '' }}>eleven_multilingual_v2 (Multi-language)</option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Creativity / Temperature (0 to 1)</label>
                            <input type="number" step="0.05" min="0" max="1" name="temperature" class="form-control" value="{{ $currentTemperature }}">
                            <small style="color: var(--text-muted); display: block; margin-top: 0.25rem;">Default: 0.55</small>
                        </div>
                    </div>
                </div>

                <!-- 3. Conversational Content -->
                <div class="form-group">
                    <label class="form-label">First Message (Greeting Sentence)</label>
                    <input type="text" name="first_message" class="form-control" value="{{ $agent['conversation_config']['agent']['first_message'] ?? '' }}" placeholder="Hi, is this Irashad? This is Emma...">
                    <small style="color: var(--text-muted); display: block; margin-top: 0.35rem;">The initial greeting phrase spoken by the AI voice agent when the recipient picks up.</small>
                </div>

                <div class="form-group">
                    <label class="form-label">System Prompt / Agent Instructions</label>
                    <textarea name="prompt" class="form-control" rows="12" style="font-family: monospace; font-size: 0.88rem; line-height: 1.5;" placeholder="Define the AI persona, rules, objective, and instructions...">{{ $agent['conversation_config']['agent']['prompt']['prompt'] ?? '' }}</textarea>
                    <small style="color: var(--text-muted); display: block; margin-top: 0.35rem;">System instructions detailing personality, tone, goals, guardrails, and conversation flow.</small>
                </div>

                <div style="margin-top: 2rem; display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem; display: flex; align-items: center; gap: 0.5rem;">
                        <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Save & Sync Settings via API
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
