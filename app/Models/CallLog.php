<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'elevenlabs_conversation_id',
        'call_status',
        'call_summary',
        'recording_url',
        'call_error_reason',
        'raw_webhook_payload'
    ];

    protected $casts = [
        'raw_webhook_payload' => 'array',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
