<?php

namespace App\Http\Controllers;

use App\Services\AgentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ChatController extends Controller
{
    protected AgentService $agentService;

    public function __construct(AgentService $agentService)
    {
        $this->agentService = $agentService;
    }

    public function index(Request $request)
    {
        $param = $request->all();

        $sessionId = Str::uuid()->toString();

        return Inertia::render('Chat', [
            'SessionId' => $param['session_id'] ?? $sessionId,
        ]);
    }

    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'session_id' => 'nullable|string',
        ]);

        // Generate session ID if not provided
        $sessionId = $request->session_id ?? Str::uuid()->toString();
        try {
            // Get conversation context (last 5 messages)
            $context = $this->agentService->getConversationHistory($sessionId, 5);

            // Process the message with AI
            $result = $this->agentService->processMessage(
                $request->message,
                $sessionId,
                $context
            );

            // Add session ID to response
            $result['session_id'] = $sessionId;

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'reply' => 'Maaf, terjadi kesalahan sistem. Silakan coba lagi.',
                'status' => 'error',
                'error' => $e->getMessage(),
                'session_id' => $sessionId
            ], 500);
        }
    }

    public function getHistory(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $history = $this->agentService->getConversationHistory(
            $request->session_id,
            $request->limit ?? 20
        );
        return response()->json([
            'success' => true,
            'history' => $history,
            'session_id' => $request->session_id
        ]);
    }

    public function clearHistory(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string',
        ]);

        // Soft delete or mark as cleared - for now, we'll just return success
        // In a real implementation, you might want to archive or delete the logs

        return response()->json([
            'success' => true,
            'message' => 'Conversation history cleared',
            'session_id' => $request->session_id
        ]);
    }
}
