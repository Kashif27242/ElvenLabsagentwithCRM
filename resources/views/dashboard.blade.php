@extends('layouts.app')

@section('header_title', 'Voice Agent Dashboard')

@section('content')
<!-- Stat Cards Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Total Leads</div>
            <div class="stat-value">{{ $stats['total_leads'] }}</div>
        </div>
        <div class="stat-icon primary">
            <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Completed Calls</div>
            <div class="stat-value">{{ $stats['completed_calls'] }}</div>
        </div>
        <div class="stat-icon success">
            <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Calls Active</div>
            <div class="stat-value">{{ $stats['in_progress_calls'] }}</div>
        </div>
        <div class="stat-icon info">
            <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Pending Calls</div>
            <div class="stat-value">{{ $stats['pending_calls'] }}</div>
        </div>
        <div class="stat-icon warning">
            <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
    </div>
</div>

<!-- ElevenLabs Agent Live Preview Banner -->
<div class="card" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); color: #ffffff; border: none; margin-bottom: 2rem;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1.25rem;">
        <div style="max-width: 650px;">
            <div style="display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(99, 102, 241, 0.25); border: 1px solid rgba(165, 180, 252, 0.2); padding: 0.35rem 0.85rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; color: #c7d2fe; margin-bottom: 0.75rem;">
                <span class="status-dot" style="background-color: #34d399;"></span>
                LIVE CONVAI WIDGET ACTIVE
            </div>
            <h3 style="font-size: 1.35rem; font-weight: 800; color: #ffffff; margin-bottom: 0.5rem;">Test Voice Agent Live</h3>
            <p style="color: #c7d2fe; font-size: 0.925rem; line-height: 1.5; margin: 0;">
                Experience real-time interactive voice conversation with your configured ElevenLabs Agent right inside the browser. Click the widget icon floating at the bottom right corner of your screen to launch a live test call.
            </p>
        </div>
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <span style="font-size: 0.85rem; font-weight: 600; color: #a5b4fc; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); padding: 0.6rem 1.1rem; border-radius: 0.6rem; font-family: monospace;">
                Agent ID: {{ env('Agent_ID', 'Not set') }}
            </span>
        </div>
    </div>
</div>

<!-- Recent Leads Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Recent Leads</h3>
        <a href="{{ route('leads.create') }}" class="btn btn-primary">
            <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add New Lead
        </a>
    </div>

    <div class="table-responsive">
        <table class="modern-table">
            <thead>
                <tr>
                    <th>Lead Name</th>
                    <th>Phone Number</th>
                    <th>Call Type</th>
                    <th>Call Status</th>
                    <th>Date Added</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentLeads as $lead)
                <tr>
                    <td><strong>{{ $lead->first_name }} {{ $lead->last_name }}</strong></td>
                    <td>{{ $lead->phone_number }}</td>
                    <td><span class="badge" style="background: #f1f5f9; color: #475569;">{{ ucfirst($lead->call_type) }}</span></td>
                    <td><span class="badge badge-{{ strtolower($lead->call_status) }}">{{ str_replace('_', ' ', $lead->call_status) }}</span></td>
                    <td>{{ $lead->created_at->format('M d, Y H:i') }}</td>
                    <td>
                        <a href="{{ route('leads.show', $lead) }}" class="btn btn-secondary" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                            View Details
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">No leads available. Click "Add New Lead" to get started.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
