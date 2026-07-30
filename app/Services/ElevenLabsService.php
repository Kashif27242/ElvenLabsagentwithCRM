<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ElevenLabsService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.elevenlabs.io/v1';

    public function __construct()
    {
        $this->apiKey = env('Eleven_labs_api', '');
    }

    /**
     * Trigger an outbound call via ElevenLabs.
     */
    public function triggerOutboundCall(string $phoneNumber, string $agentId, array $dynamicVariables = []): array
    {
        $phoneNumberId = env('Agent_Phone_Number_ID', '');

        // Fallback: If Agent_Phone_Number_ID is missing in env, fetch available phone numbers
        if (empty($phoneNumberId)) {
            $phoneRes = Http::withHeaders([
                'xi-api-key' => $this->apiKey,
            ])->get("{$this->baseUrl}/convai/phone-numbers");

            if ($phoneRes->successful() && !empty($phoneRes->json())) {
                $numbers = $phoneRes->json();
                $phoneNumberId = $numbers[0]['phone_number_id'] ?? '';
            }
        }

        if (empty($phoneNumberId)) {
            return [
                'success' => false,
                'error' => 'No phone_number_id found in ElevenLabs account or .env (Agent_Phone_Number_ID).'
            ];
        }

        $payload = [
            'agent_id' => $agentId,
            'agent_phone_number_id' => $phoneNumberId,
            'to_number' => $phoneNumber,
        ];

        if (!empty($dynamicVariables)) {
            $payload['dynamic_variables'] = $dynamicVariables;
        }

        $response = Http::withHeaders([
            'xi-api-key' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/convai/twilio/outbound-call", $payload);

        $data = $response->json();

        if ($response->successful() && isset($data['success']) && $data['success'] !== false) {
            return [
                'success' => true,
                'conversation_id' => $data['conversation_id'] ?? null,
                'data' => $data,
            ];
        }

        $errorReason = 'ElevenLabs API Error';
        if (isset($data['message'])) {
            $errorReason = $data['message'];
        } elseif (isset($data['detail'])) {
            $errorReason = is_array($data['detail']) ? json_encode($data['detail']) : $data['detail'];
        } else {
            $errorReason = "HTTP Status {$response->status()}: " . $response->body();
        }

        Log::error('ElevenLabs Outbound Call Failed', [
            'status' => $response->status(),
            'body' => $response->body(),
            'parsed_reason' => $errorReason
        ]);

        return [
            'success' => false,
            'error' => $errorReason,
            'data' => $data
        ];
    }

    /**
     * Get details and settings for an Agent.
     */
    public function getAgentDetails(string $agentId): ?array
    {
        $response = Http::withHeaders([
            'xi-api-key' => $this->apiKey,
        ])->get("{$this->baseUrl}/convai/agents/{$agentId}");

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Failed to fetch ElevenLabs Agent details', [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        return null;
    }

    /**
     * Update configuration for an Agent.
     */
    public function updateAgentDetails(string $agentId, array $payload): ?array
    {
        $response = Http::withHeaders([
            'xi-api-key' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->patch("{$this->baseUrl}/convai/agents/{$agentId}", $payload);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Failed to update ElevenLabs Agent details', [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        return null;
    }

    /**
     * Fetch list of all conversations from ElevenLabs.
     */
    public function getConversations(int $pageSize = 30): ?array
    {
        $response = Http::withHeaders([
            'xi-api-key' => $this->apiKey,
        ])->get("{$this->baseUrl}/convai/conversations", [
            'page_size' => $pageSize
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Failed to fetch ElevenLabs conversations', [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        return null;
    }

    /**
     * Fetch single conversation details (transcript, status, metadata).
     */
    public function getConversationDetails(string $conversationId): ?array
    {
        $response = Http::withHeaders([
            'xi-api-key' => $this->apiKey,
        ])->get("{$this->baseUrl}/convai/conversations/{$conversationId}");

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Failed to fetch ElevenLabs conversation details', [
            'conversation_id' => $conversationId,
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        return null;
    }
}
