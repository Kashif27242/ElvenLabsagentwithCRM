<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'phone_number',
        'email',
        'company',
        'context',
        'call_type',
        'call_delay_minutes',
        'status',
        'call_status',
        'elevenlabs_conversation_id',
        'call_summary',
        'recording_url',
        'call_error_reason'
    ];

    public function callLogs(): HasMany
    {
        return $this->hasMany(CallLog::class)->orderBy('created_at', 'desc');
    }
}
