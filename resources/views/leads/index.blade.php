@extends('layouts.app')

@section('header_title', 'All Leads')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Leads Directory</h3>
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
                    <th>Email</th>
                    <th>Company</th>
                    <th>Call Type</th>
                    <th>Call Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leads as $lead)
                <tr>
                    <td><strong>{{ $lead->first_name }} {{ $lead->last_name }}</strong></td>
                    <td>{{ $lead->phone_number }}</td>
                    <td>{{ $lead->email ?: 'N/A' }}</td>
                    <td>{{ $lead->company ?: 'N/A' }}</td>
                    <td><span class="badge" style="background: #f1f5f9; color: #475569;">{{ ucfirst($lead->call_type) }}</span></td>
                    <td><span class="badge badge-{{ strtolower($lead->call_status) }}">{{ str_replace('_', ' ', $lead->call_status) }}</span></td>
                    <td>
                        <a href="{{ route('leads.show', $lead) }}" class="btn btn-secondary" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                            View Details
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">No leads found. Click "Add New Lead" to create your first lead.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
