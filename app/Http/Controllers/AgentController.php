<?php

namespace App\Http\Controllers;

use App\Services\ElevenLabsService;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    protected ElevenLabsService $elevenLabsService;

    public function __construct(ElevenLabsService $elevenLabsService)
    {
        $this->elevenLabsService = $elevenLabsService;
    }

    public function edit()
    {
        $agentId = env('Agent_ID', '');
        $agent = null;

        if ($agentId) {
            $agent = $this->elevenLabsService->getAgentDetails($agentId);
        }

        return view('agent.settings', compact('agent', 'agentId'));
    }

    public function update(Request $request)
    {
        $agentId = env('Agent_ID', '');
        if (!$agentId) {
            return redirect()->back()->with('error', 'Agent ID not set in .env');
        }

        $payload = [];

        if ($request->has('name')) {
            $payload['name'] = $request->input('name');
        }

        // Build conversation_config payload if prompt or first_message updated
        $conversationConfig = [];
        if ($request->filled('prompt')) {
            $conversationConfig['agent']['prompt']['prompt'] = $request->input('prompt');
        }
        if ($request->filled('first_message')) {
            $conversationConfig['agent']['first_message'] = $request->input('first_message');
        }

        if (!empty($conversationConfig)) {
            $payload['conversation_config'] = $conversationConfig;
        }

        $result = $this->elevenLabsService->updateAgentDetails($agentId, $payload);

        if ($result) {
            return redirect()->back()->with('success', 'Agent settings updated successfully in ElevenLabs!');
        }

        return redirect()->back()->with('error', 'Failed to update agent settings. Check logs.');
    }
}
