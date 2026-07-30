@extends('layouts.app')

@section('header_title', 'Create Lead')

@section('content')
<div style="max-width: 800px; margin: 0 auto;">
    <div class="card" x-data="{ callType: 'manual' }">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 1rem; margin-bottom: 1.5rem;">
            <h3 class="card-title">New Lead Details</h3>
            <a href="{{ route('leads.index') }}" class="btn btn-secondary">Back to List</a>
        </div>

        <form action="{{ route('leads.store') }}" method="POST">
            @csrf
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                <div class="form-group">
                    <label class="form-label">First Name *</label>
                    <input type="text" name="first_name" class="form-control" required placeholder="John">
                </div>

                <div class="form-group">
                    <label class="form-label">Last Name *</label>
                    <input type="text" name="last_name" class="form-control" required placeholder="Doe">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                <div class="form-group">
                    <label class="form-label">Phone Number *</label>
                    <input type="text" name="phone_number" class="form-control" required placeholder="+1234567890">
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="john@example.com">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Company Name</label>
                <input type="text" name="company" class="form-control" placeholder="Acme Corp">
            </div>

            <div class="form-group">
                <label class="form-label">Context for 3knot Digital Voice Agent</label>
                <textarea name="context" class="form-control" rows="3" placeholder="Add custom instructions or background info for the AI agent (e.g. Lead requested a demo on enterprise pricing)..."></textarea>
            </div>

            <div class="form-group" style="margin-top: 1.5rem;">
                <label class="form-label">Call Dispatch Strategy</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.5rem;">
                    <div class="call-option-card" :class="{ 'selected': callType === 'manual' }" @click="callType = 'manual'">
                        <input type="radio" name="call_type" value="manual" x-model="callType" style="margin-top: 3px;">
                        <div>
                            <strong style="display: block; font-size: 0.95rem;">Manual Call</strong>
                            <small style="color: var(--text-muted);">Trigger the call manually from the lead details page.</small>
                        </div>
                    </div>

                    <div class="call-option-card" :class="{ 'selected': callType === 'auto' }" @click="callType = 'auto'">
                        <input type="radio" name="call_type" value="auto" x-model="callType" style="margin-top: 3px;">
                        <div>
                            <strong style="display: block; font-size: 0.95rem;">Automatic Call</strong>
                            <small style="color: var(--text-muted);">Schedule the AI agent to call automatically after creation.</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group" x-show="callType === 'auto'" x-transition style="margin-top: 1.25rem;">
                <label class="form-label">Call Delay (Minutes)</label>
                <input type="number" name="call_delay_minutes" class="form-control" value="0" min="0" placeholder="0">
                <small style="color: var(--text-muted); display: block; margin-top: 0.35rem;">Enter 0 to call immediately upon save, or enter minutes to wait.</small>
            </div>

            <div style="margin-top: 2rem; display: flex; justify-content: flex-end; gap: 1rem;">
                <a href="{{ route('leads.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem;">Save Lead & Proceed</button>
            </div>
        </form>
    </div>
</div>
@endsection
