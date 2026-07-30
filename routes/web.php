<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\ConversationController;

Route::get('/', [LeadController::class, 'dashboard'])->name('dashboard');
Route::resource('leads', LeadController::class)->only(['index', 'create', 'store', 'show']);
Route::post('leads/{lead}/trigger-call', [LeadController::class, 'triggerCall'])->name('leads.trigger-call');
Route::get('leads/{lead}/check-status', [LeadController::class, 'checkStatus'])->name('leads.check-status');

// ElevenLabs Conversations Routes
Route::get('conversations', [ConversationController::class, 'index'])->name('conversations.index');
Route::get('conversations/{conversationId}', [ConversationController::class, 'show'])->name('conversations.show');
Route::get('conversations/{conversationId}/audio', [ConversationController::class, 'audio'])->name('conversations.audio');

// ElevenLabs Agent Management Routes
Route::get('agent/settings', [AgentController::class, 'edit'])->name('agent.edit');
Route::put('agent/settings', [AgentController::class, 'update'])->name('agent.update');
