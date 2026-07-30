<?php

namespace App\Services;

use App\Models\Lead;
use App\Jobs\InitiateOutboundCallJob;

class LeadService
{
    /**
     * Create a new lead and schedule an outbound call if necessary.
     *
     * @param array $data
     * @return Lead
     */
    public function createLead(array $data): Lead
    {
        $lead = Lead::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone_number' => $data['phone_number'],
            'email' => $data['email'] ?? null,
            'company' => $data['company'] ?? null,
            'context' => $data['context'] ?? null,
            'call_type' => $data['call_type'] ?? 'manual',
            'call_delay_minutes' => $data['call_delay_minutes'] ?? null,
            'status' => 'new',
            'call_status' => 'pending',
        ]);

        if ($lead->call_type === 'auto') {
            $delay = now()->addMinutes((int)$lead->call_delay_minutes);
            InitiateOutboundCallJob::dispatch($lead)->delay($delay);
        }

        return $lead;
    }

    /**
     * Manually trigger a call for a lead.
     *
     * @param Lead $lead
     * @return void
     */
    public function triggerCall(Lead $lead): void
    {
        InitiateOutboundCallJob::dispatch($lead);
    }
}
