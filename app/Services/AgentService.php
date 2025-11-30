<?php

namespace App\Services;

use App\MCP\Tools\ToolRegistry;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AgentService
{
    private ?string $openaiApiKey;
    private ?string $openRouterApiKey;
    private string $aiProvider;
    private array $functionSchemas;

    public function __construct()
    {
        $this->openaiApiKey = env('OPENAI_API_KEY');
        $this->openRouterApiKey = env('OPENROUTER_API_KEY', '');
        $this->aiProvider = env('AI_PROVIDER', 'openai'); // 'openai' or 'openrouter'
        $this->loadFunctionSchemas();

        // Check if API keys are configured
        if ($this->aiProvider === 'openai' && ($this->openaiApiKey === 'your-openai-api-key-here' || empty($this->openaiApiKey))) {
            Log::warning('OpenAI API key not configured. AI chat will use rule-based fallback.');
        }

        if ($this->aiProvider === 'openrouter' && empty($this->openRouterApiKey)) {
            Log::warning('OpenRouter API key not configured. AI chat will use rule-based fallback.');
        }
    }

    private function loadFunctionSchemas(): void
    {
        ToolRegistry::autoDiscover();
        $this->functionSchemas = ToolRegistry::getToolSchemas();
    }

    public function processMessage(string $message, string $sessionId, array $context = []): array
    {
        // First check if this is a known pattern that should use rule-based processing
        $ruleBasedResult = $this->tryRuleBasedFirst($message, $sessionId);
        if ($ruleBasedResult !== null) {
            return $ruleBasedResult;
        }

        // Try AI provider for complex queries
        $aiAvailable = false;

        if ($this->aiProvider === 'openai' && $this->openaiApiKey !== 'your-openai-api-key-here' && !empty($this->openaiApiKey)) {
            $aiAvailable = true;
        } elseif ($this->aiProvider === 'openrouter' && !empty($this->openRouterApiKey)) {
            $aiAvailable = true;
        }

        if ($aiAvailable) {
            try {
                // Create conversation context
                $messages = $this->buildConversationContext($message, $context);

                // Call AI provider with function calling
                $response = $this->callAIProvider($messages);

                // Process the response
                $result = $this->processAIResponse($response, $sessionId, $message);

                // If AI successfully processed it (called a tool or gave a meaningful response), return it
                if ($result['executed_tool'] !== null || strpos($result['reply'], 'Maaf, saya belum mengerti') === false) {
                    return $result;
                }

                // If AI didn't understand, fall back to rule-based AI
                Log::info('AI provider did not understand, falling back to rule-based AI');
            } catch (\Exception $e) {
                Log::error('AI provider API call failed, falling back to rule-based AI', [
                    'provider' => $this->aiProvider,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Use rule-based AI as final fallback
        return $this->processWithRuleBasedAI($message, $sessionId);
    }

    private function tryRuleBasedFirst(string $message, string $sessionId): ?array
    {
        $message = strtolower(trim($message));

        // Quick pattern checks for rule-based processing
        if ($this->isGreeting($message)) {
            return $this->handleGreeting($message, $sessionId);
        }

        if ($this->isCapabilityQuestion($message)) {
            return $this->handleCapabilityQuestion($message, $sessionId);
        }

        if ($this->isThanks($message)) {
            return $this->handleThanks($message, $sessionId);
        }

        if ($this->isDatabaseQuery($message)) {
            return $this->handleDatabaseQuery($message, $sessionId);
        }

        // Check for business operation patterns
        if (strpos($message, 'buat') !== false && strpos($message, 'customer') !== false) {
            return $this->handleCreateUserIntent($message, $sessionId);
        }

        if (strpos($message, 'update') !== false && strpos($message, 'task') !== false) {
            return $this->handleUpdateTaskIntent($message, $sessionId);
        }

        if (strpos($message, 'cari') !== false && strpos($message, 'task') !== false) {
            return $this->handleSearchTasksIntent($message, $sessionId);
        }

        return null; // Not a rule-based pattern, try AI
    }

    private function buildConversationContext(string $message, array $context): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => $this->getSystemPrompt()
            ]
        ];

        // Add previous context if available
        foreach ($context as $ctx) {
            $messages[] = [
                'role' => 'user',
                'content' => $ctx['user_message']
            ];
            $messages[] = [
                'role' => 'assistant',
                'content' => $ctx['ai_response']
            ];
        }

        // Add current message
        $messages[] = [
            'role' => 'user',
            'content' => $message
        ];

        return $messages;
    }

    private function getSystemPrompt(): string
    {
        return "You are an AI assistant for a business management system. You MUST use the available tools to perform actions - do not make up information or simulate actions.

AVAILABLE TOOLS:
- create_customer: Create new customers with name, email, phone
- get_user: Find users by email address
- search_tasks: Find tasks by status (pending/in_progress/completed) and/or assigned user
- update_task_status: Change task status (task_id and new status required)
- get_overdue_customers: Find customers inactive for X days

CRITICAL RULES:
1. When user asks about data (counts, lists, searches), ALWAYS call the appropriate tool
2. When user wants to create/update/delete, ALWAYS call the appropriate tool
3. Never make up data - always use tools to get real information from database
4. For business operations, respond with tool results only
5. For simple greetings/questions, respond normally in Indonesian
6. Be concise but informative about what was accomplished

TOOL USAGE EXAMPLES:
- User: 'tampilkan customer yang belum dihubungi lebih dari 1 hari' → Call get_overdue_customers with days=1
- User: 'buat customer baru' → Call create_customer
- User: 'cari task pending' → Call search_tasks with status=pending";
    }

    private function callAIProvider(array $messages): array
    {
        if ($this->aiProvider === 'openrouter') {
            return $this->callOpenRouter($messages);
        } else {
            return $this->callOpenAI($messages);
        }
    }

    private function callOpenAI(array $messages): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->openaiApiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages,
            'tools' => $this->functionSchemas,
            'tool_choice' => 'auto',
            'temperature' => 0.1,
            'max_tokens' => 1000,
        ]);

        if (!$response->successful()) {
            // Log the error for debugging
            Log::error('OpenAI API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'headers' => $response->headers()
            ]);

            throw new \Exception('OpenAI API request failed: ' . $response->status() . ' - ' . $response->body());
        }

        $data = $response->json();

        // Log the response for debugging
        Log::info('OpenAI API Response', ['response' => $data]);

        return $data;
    }


    private function callOpenRouter(array $messages): array
    {
        // Try different tool formats for OpenRouter compatibility
        $requestData = [
            'model' => 'openai/gpt-4o',
            'messages' => $messages,
            'temperature' => 0.1,
            'max_tokens' => 1000,
        ];

        // Try tools format first (OpenAI style)
        $requestData['tools'] = $this->functionSchemas;
        $requestData['tool_choice'] = 'auto';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->openRouterApiKey,
            'Content-Type' => 'application/json',
            'HTTP-Referer' => request()->getHost(),
            'X-Title' => 'MCP AI Chat Assistant',
        ])->post('https://openrouter.ai/api/v1/chat/completions', $requestData);

        if (!$response->successful()) {
            // Log the error for debugging
            Log::error('OpenRouter API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'headers' => $response->headers(),
                'request' => $requestData
            ]);

            throw new \Exception('OpenRouter API request failed: ' . $response->status() . ' - ' . $response->body());
        }

        $data = $response->json();

        // Log the response for debugging
        Log::info('OpenRouter API Response', [
            'has_tool_calls' => isset($data['choices'][0]['message']['tool_calls']),
            'response' => $data
        ]);

        return $data;
    }

    private function processWithRuleBasedAI(string $message, string $sessionId): array
    {
        $message = strtolower(trim($message));

        // Handle simple greetings first
        if ($this->isGreeting($message)) {
            return $this->handleGreeting($message, $sessionId);
        }

        // Handle capability questions
        if ($this->isCapabilityQuestion($message)) {
            return $this->handleCapabilityQuestion($message, $sessionId);
        }

        // Handle thanks and acknowledgments
        if ($this->isThanks($message)) {
            return $this->handleThanks($message, $sessionId);
        }

        // Handle direct database queries
        if ($this->isDatabaseQuery($message)) {
            return $this->handleDatabaseQuery($message, $sessionId);
        }

        // Simple rule-based intent detection
        if (strpos($message, 'buat') !== false && strpos($message, 'user') !== false) {
            return $this->handleCreateUserIntent($message, $sessionId);
        }

        if (strpos($message, 'cari') !== false && strpos($message, 'task') !== false) {
            return $this->handleSearchTasksIntent($message, $sessionId);
        }

        if (strpos($message, 'update') !== false && strpos($message, 'task') !== false) {
            return $this->handleUpdateTaskIntent($message, $sessionId);
        }

        if (strpos($message, 'customer') !== false && (strpos($message, 'belum') !== false || strpos($message, 'overdue') !== false)) {
            return $this->handleOverdueCustomersIntent($message, $sessionId);
        }

        // Default response
        $reply = 'Maaf, saya belum mengerti permintaan Anda. Coba katakan "Buatkan user baru dengan nama [nama] email [email]" atau "Cari task yang statusnya pending".';

        // Log the unrecognized interaction
        $this->logInteraction($sessionId, $message, ['type' => 'unrecognized'], [], null, $reply);

        return [
            'reply' => $reply,
            'executed_tool' => null,
            'arguments' => null,
            'result' => null,
            'status' => 'success'
        ];
    }

    private function handleCapabilityQuestion(string $message, string $sessionId): array
    {
        $reply = 'Saya adalah asisten AI untuk sistem manajemen bisnis. Saya bisa membantu Anda dengan:

• Membuat customer baru: "Buatkan user baru dengan nama [nama] email [email]"
• Mencari task: "Cari semua task yang statusnya pending"
• Update status task: "Update status task nomor 1 menjadi completed"
• Melihat customer yang belum dihubungi: "Tampilkan customer yang belum dihubungi lebih dari 30 hari"
• Mendapatkan info user: "Tampilkan info user dengan email [email]"

Coba salah satu contoh di atas atau tanyakan apa saja yang ingin Anda kelola!';

        // Log the capability question interaction
        $this->logInteraction($sessionId, $message, ['type' => 'capability_question'], [], null, $reply);

        return [
            'reply' => $reply,
            'executed_tool' => null,
            'arguments' => null,
            'result' => null,
            'status' => 'success'
        ];
    }

    private function isGreeting(string $message): bool
    {
        $greetings = ['halo', 'hello', 'hi', 'hai', 'selamat', 'pagi', 'siang', 'sore', 'malam', 'hey'];
        foreach ($greetings as $greeting) {
            if (strpos($message, $greeting) !== false) {
                return true;
            }
        }
        return false;
    }

    private function isCapabilityQuestion(string $message): bool
    {
        $questions = ['apa yang bisa', 'kamu bisa', 'bisa apa', 'fungsi', 'kemampuan', 'help', 'bantuan'];
        foreach ($questions as $question) {
            if (strpos($message, $question) !== false) {
                return true;
            }
        }
        return false;
    }

    private function isThanks(string $message): bool
    {
        $thanks = ['terima kasih', 'thanks', 'thank you', 'makasih', 'thx', 'tq'];
        foreach ($thanks as $thank) {
            if (strpos($message, $thank) !== false) {
                return true;
            }
        }
        return false;
    }

    private function isDatabaseQuery(string $message): bool
    {
        $queries = [
            'berapa banyak', 'berapa jumlah', 'ada berapa',
            'total', 'jumlah', 'count', 'hitung',
            'daftar', 'list', 'lihat semua'
        ];

        $entities = ['customer', 'task', 'user', 'pelanggan', 'tugas', 'pengguna'];

        foreach ($queries as $query) {
            foreach ($entities as $entity) {
                if (strpos($message, $query) !== false && strpos($message, $entity) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    private function handleGreeting(string $message, string $sessionId): array
    {
        $responses = [
            'Halo! Saya adalah asisten AI untuk membantu Anda mengelola data bisnis. Saya bisa membuat user baru, mencari task, update status task, dan lainnya. Apa yang bisa saya bantu hari ini?',
            'Hai! Selamat datang di sistem manajemen bisnis AI. Saya siap membantu Anda dengan berbagai tugas seperti membuat customer baru atau mengelola task. Ada yang bisa saya bantu?',
            'Hello! Saya asisten AI yang bisa membantu Anda dengan operasi bisnis. Coba katakan "Buatkan user baru" atau "Cari task pending" untuk melihat kemampuan saya.',
            'Selamat datang! Saya adalah AI assistant untuk sistem bisnis ini. Saya bisa membantu Anda mengelola customer, task, dan data lainnya. Silakan beri tahu apa yang Anda butuhkan.'
        ];

        $reply = $responses[array_rand($responses)];

        // Log the greeting interaction
        $this->logInteraction($sessionId, $message, ['type' => 'greeting'], [], null, $reply);

        return [
            'reply' => $reply,
            'executed_tool' => null,
            'arguments' => null,
            'result' => null,
            'status' => 'success'
        ];
    }

    private function handleDatabaseQuery(string $message, string $sessionId): array
    {
        $message = strtolower($message);

        // Handle customer queries
        if (strpos($message, 'customer') !== false || strpos($message, 'pelanggan') !== false) {
            $count = \App\Models\Customer::count();
            $reply = "Ada {$count} customer di database.";

            $this->logInteraction($sessionId, $message, ['type' => 'database_query', 'entity' => 'customers'], [], ['count' => $count], $reply);

            return [
                'reply' => $reply,
                'executed_tool' => 'database_query',
                'arguments' => ['entity' => 'customers'],
                'result' => ['count' => $count],
                'status' => 'success'
            ];
        }

        // Handle task queries
        if (strpos($message, 'task') !== false || strpos($message, 'tugas') !== false) {
            $total = \App\Models\Task::count();
            $pending = \App\Models\Task::where('status', 'pending')->count();
            $completed = \App\Models\Task::where('status', 'completed')->count();
            $inProgress = \App\Models\Task::where('status', 'in_progress')->count();

            $reply = "Ada {$total} task total: {$pending} pending, {$inProgress} in progress, {$completed} completed.";

            $this->logInteraction($sessionId, $message, ['type' => 'database_query', 'entity' => 'tasks'], [],
                ['total' => $total, 'pending' => $pending, 'completed' => $completed, 'in_progress' => $inProgress], $reply);

            return [
                'reply' => $reply,
                'executed_tool' => 'database_query',
                'arguments' => ['entity' => 'tasks'],
                'result' => ['total' => $total, 'pending' => $pending, 'completed' => $completed, 'in_progress' => $inProgress],
                'status' => 'success'
            ];
        }

        // Default database query response
        $reply = 'Maaf, saya tidak mengerti query database yang Anda maksud. Coba tanyakan "berapa banyak customer?" atau "ada berapa task?".';

        $this->logInteraction($sessionId, $message, ['type' => 'database_query', 'entity' => 'unknown'], [], [], $reply);

        return [
            'reply' => $reply,
            'executed_tool' => null,
            'arguments' => null,
            'result' => null,
            'status' => 'success'
        ];
    }

    private function handleCreateUserIntent(string $message, string $sessionId): array
    {
        // Extract name and email using regex
        $name = $this->extractName($message);
        $email = $this->extractEmail($message);
        $phone = $this->extractPhone($message);

        if (!$name || !$email) {
            return [
                'reply' => 'Saya perlu nama dan email untuk membuat user. Contoh: "Buatkan user baru dengan nama John email john@example.com"',
                'executed_tool' => null,
                'arguments' => null,
                'result' => null,
                'status' => 'error'
            ];
        }

        try {
            $tool = ToolRegistry::getTool('create_customer');
            $args = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone
            ];
            $result = $tool->execute($args);

            $reply = "Customer '{$name}' berhasil dibuat dengan ID {$result['id']}.";

            $this->logInteraction($sessionId, $message, ['name' => 'create_customer'], $args, $result, $reply);

            return [
                'reply' => $reply,
                'executed_tool' => 'create_customer',
                'arguments' => $args,
                'result' => $result,
                'status' => 'success'
            ];
        } catch (\Exception $e) {
            return [
                'reply' => 'Maaf, terjadi kesalahan saat membuat user: ' . $e->getMessage(),
                'executed_tool' => 'create_customer',
                'arguments' => ['name' => $name, 'email' => $email],
                'result' => null,
                'status' => 'error'
            ];
        }
    }

    private function handleThanks(string $message, string $sessionId): array
    {
        $responses = [
            'Sama-sama! Senang bisa membantu Anda. Ada lagi yang bisa saya bantu?',
            'Terima kasih kembali! Saya di sini jika Anda butuh bantuan lagi.',
            'Tidak masalah! Silakan hubungi saya kapan saja jika ada yang bisa saya bantu.',
            'Senang bisa membantu! Apa ada hal lain yang ingin Anda kelola?'
        ];

        $reply = $responses[array_rand($responses)];

        // Log the thanks interaction
        $this->logInteraction($sessionId, $message, ['type' => 'thanks'], [], null, $reply);

        return [
            'reply' => $reply,
            'executed_tool' => null,
            'arguments' => null,
            'result' => null,
            'status' => 'success'
        ];
    }

    private function handleSearchTasksIntent(string $message, string $sessionId): array
    {
        $status = null;
        if (strpos($message, 'pending') !== false) {
            $status = 'pending';
        } elseif (strpos($message, 'progress') !== false) {
            $status = 'in_progress';
        } elseif (strpos($message, 'completed') !== false) {
            $status = 'completed';
        }

        try {
            $tool = ToolRegistry::getTool('search_tasks');
            $args = array_filter(['status' => $status]);
            $result = $tool->execute($args);

            $count = count($result);
            $statusText = $status ? "status {$status}" : "semua";
            $reply = "Ditemukan {$count} task dengan {$statusText}.";

            $this->logInteraction($sessionId, $message, ['name' => 'search_tasks'], $args, $result, $reply);

            return [
                'reply' => $reply,
                'executed_tool' => 'search_tasks',
                'arguments' => $args,
                'result' => $result,
                'status' => 'success'
            ];
        } catch (\Exception $e) {
            return [
                'reply' => 'Maaf, terjadi kesalahan saat mencari task: ' . $e->getMessage(),
                'executed_tool' => 'search_tasks',
                'arguments' => $args,
                'result' => null,
                'status' => 'error'
            ];
        }
    }

    private function handleUpdateTaskIntent(string $message, string $sessionId): array
    {
        // Extract task ID
        preg_match('/task nomor (\d+)/', $message, $matches);
        $taskId = $matches[1] ?? null;

        $status = null;
        if (strpos($message, 'completed') !== false) {
            $status = 'completed';
        } elseif (strpos($message, 'pending') !== false) {
            $status = 'pending';
        } elseif (strpos($message, 'progress') !== false) {
            $status = 'in_progress';
        }

        if (!$taskId || !$status) {
            return [
                'reply' => 'Saya perlu nomor task dan status baru. Contoh: "Update status task nomor 1 menjadi completed"',
                'executed_tool' => null,
                'arguments' => null,
                'result' => null,
                'status' => 'error'
            ];
        }

        try {
            $tool = ToolRegistry::getTool('update_task_status');
            $args = ['task_id' => (int)$taskId, 'status' => $status];
            $result = $tool->execute($args);

            if (isset($result['error'])) {
                $reply = "Task dengan ID {$taskId} tidak ditemukan.";
            } else {
                $reply = "Status task ID {$result['id']} berhasil diubah menjadi '{$result['status']}'.";
            }

            $this->logInteraction($sessionId, $message, ['name' => 'update_task_status'], $args, $result, $reply);

            return [
                'reply' => $reply,
                'executed_tool' => 'update_task_status',
                'arguments' => $args,
                'result' => $result,
                'status' => 'success'
            ];
        } catch (\Exception $e) {
            return [
                'reply' => 'Maaf, terjadi kesalahan saat update task: ' . $e->getMessage(),
                'executed_tool' => 'update_task_status',
                'arguments' => $args,
                'result' => null,
                'status' => 'error'
            ];
        }
    }

    private function handleOverdueCustomersIntent(string $message, string $sessionId): array
    {
        // Extract days (default 30)
        preg_match('/(\d+)\s*hari/', $message, $matches);
        $days = $matches[1] ?? 30;

        try {
            $tool = ToolRegistry::getTool('get_overdue_customers');
            $args = ['days' => (int)$days];
            $result = $tool->execute($args);

            $count = count($result);
            $reply = "Ditemukan {$count} customer yang belum dihubungi lebih dari {$days} hari.";

            $this->logInteraction($sessionId, $message, ['name' => 'get_overdue_customers'], $args, $result, $reply);

            return [
                'reply' => $reply,
                'executed_tool' => 'get_overdue_customers',
                'arguments' => $args,
                'result' => $result,
                'status' => 'success'
            ];
        } catch (\Exception $e) {
            return [
                'reply' => 'Maaf, terjadi kesalahan saat mencari customer overdue: ' . $e->getMessage(),
                'executed_tool' => 'get_overdue_customers',
                'arguments' => $args,
                'result' => null,
                'status' => 'error'
            ];
        }
    }

    private function extractName(string $message): ?string
    {
        // Try different patterns - stop at email or phone
        $patterns = [
            '/nama\s+([A-Za-z\s]+?)(?:\s+email|\s+e-mail|\s+no|\s+nomor|\s+phone|$)/i',
            '/dengan nama\s+([A-Za-z\s]+?)(?:\s+email|\s+e-mail|\s+no|\s+nomor|\s+phone|$)/i',
            '/nama:\s*([A-Za-z\s]+?)(?:\s+email|\s+e-mail|\s+no|\s+nomor|\s+phone|$)/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }

    private function extractEmail(string $message): ?string
    {
        if (preg_match('/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/', $message, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private function extractPhone(string $message): ?string
    {
        // Extract phone numbers
        $patterns = [
            '/(?:no|nomor|phone|telp)\s*[:\-]?\s*([0-9+\-\s]+)/i',
            '/([0-9]{10,15})/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }

    private function processAIResponse(array $response, string $sessionId, string $originalMessage): array
    {
        if (!isset($response['choices'][0]['message'])) {
            Log::error('Invalid OpenAI response structure', ['response' => $response]);
            return [
                'reply' => 'Maaf, terjadi kesalahan dalam memproses permintaan Anda.',
                'status' => 'error',
                'error' => 'Invalid AI response structure'
            ];
        }

        $message = $response['choices'][0]['message'];

        // Check if AI wants to call a tool (new API format)
        if (isset($message['tool_calls']) && is_array($message['tool_calls'])) {
            // Handle tool calls - this is the first phase
            $toolCall = $message['tool_calls'][0];
            $toolResult = $this->executeToolCall($toolCall, $sessionId, $originalMessage);

            // If tool was executed successfully, we should get a final response from AI
            // But for now, return the tool result directly
            return $toolResult;
        }

        // Check if AI wants to call a function (legacy format)
        if (isset($message['function_call'])) {
            return $this->executeFunctionCall($message['function_call'], $sessionId, $originalMessage);
        }

        // Regular response without tool/function call
        $reply = $message['content'] ?? 'Permintaan dipahami, tetapi tidak ada aksi yang diperlukan.';

        // Log the conversational interaction
        $this->logInteraction($sessionId, $originalMessage, ['type' => 'conversation'], [], null, $reply);

        return [
            'reply' => $reply,
            'executed_tool' => null,
            'arguments' => null,
            'result' => null,
            'status' => 'success'
        ];
    }

    private function executeToolCall(array $toolCall, string $sessionId, string $originalMessage): array
    {
        $toolName = $toolCall['function']['name'];
        $args = json_decode($toolCall['function']['arguments'], true);

        try {
            // Get the tool
            $tool = ToolRegistry::getTool($toolName);
            if (!$tool) {
                throw new \Exception("Tool '{$toolName}' not found");
            }

            // Execute the tool
            $result = $tool->execute($args);

            // Generate human-readable response
            $reply = $this->generateHumanResponse($toolName, $args, $result);

            // Log the interaction
            $this->logInteraction($sessionId, $originalMessage, $toolCall, $args, $result, $reply);

            return [
                'reply' => $reply,
                'executed_tool' => $toolName,
                'arguments' => $args,
                'result' => $result,
                'status' => 'success'
            ];
        } catch (\Exception $e) {
            $errorMessage = 'Maaf, terjadi kesalahan saat menjalankan perintah: ' . $e->getMessage();

            $this->logInteraction($sessionId, $originalMessage, $toolCall, $args, null, $errorMessage, 'error', $e->getMessage());

            return [
                'reply' => $errorMessage,
                'executed_tool' => $toolName,
                'arguments' => $args,
                'result' => null,
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }

    private function executeFunctionCall(array $functionCall, string $sessionId, string $originalMessage): array
    {
        $toolName = $functionCall['name'];
        $args = json_decode($functionCall['arguments'], true);

        try {
            // Get the tool
            $tool = ToolRegistry::getTool($toolName);
            if (!$tool) {
                throw new \Exception("Tool '{$toolName}' not found");
            }

            // Execute the tool
            $result = $tool->execute($args);

            // Generate human-readable response
            $reply = $this->generateHumanResponse($toolName, $args, $result);

            // Log the interaction
            $this->logInteraction($sessionId, $originalMessage, $functionCall, $args, $result, $reply);

            return [
                'reply' => $reply,
                'executed_tool' => $toolName,
                'arguments' => $args,
                'result' => $result,
                'status' => 'success'
            ];
        } catch (\Exception $e) {
            $errorMessage = 'Maaf, terjadi kesalahan saat menjalankan perintah: ' . $e->getMessage();

            $this->logInteraction($sessionId, $originalMessage, $functionCall, $args, null, $errorMessage, 'error', $e->getMessage());

            return [
                'reply' => $errorMessage,
                'executed_tool' => $toolName,
                'arguments' => $args,
                'result' => null,
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }

    private function generateHumanResponse(string $toolName, array $args, array $result): string
    {
        switch ($toolName) {
            case 'create_customer':
                return "Customer '{$args['name']}' berhasil dibuat dengan ID {$result['id']}.";

            case 'get_user':
                if (isset($result['error'])) {
                    return "User dengan email '{$args['email']}' tidak ditemukan.";
                }
                return "User ditemukan: {$result['name']} ({$result['email']})";

            case 'update_task_status':
                if (isset($result['error'])) {
                    return "Task dengan ID {$args['task_id']} tidak ditemukan.";
                }
                return "Status task ID {$result['id']} berhasil diubah menjadi '{$result['status']}'.";

            case 'search_tasks':
                $count = count($result);
                return "Ditemukan {$count} task yang sesuai dengan kriteria pencarian.";

            case 'get_overdue_customers':
                $count = count($result);
                return "Ditemukan {$count} customer yang belum dihubungi lebih dari {$args['days']} hari.";

            default:
                return "Aksi berhasil dijalankan.";
        }
    }

    private function logInteraction(
        string $sessionId,
        string $userMessage,
        ?array $functionCall,
        array $args,
        ?array $result,
        string $aiResponse,
        string $status = 'success',
        ?string $errorMessage = null
    ): void {
        \App\Models\MCPCommandLog::create([
            'session_id' => $sessionId,
            'user_message' => $userMessage,
            'ai_analysis' => $functionCall,
            'detected_tool' => $functionCall['name'] ?? null,
            'extracted_args' => $args,
            'tool_result' => $result,
            'ai_response' => $aiResponse,
            'status' => $status,
            'error_message' => $errorMessage,
            'ip_address' => request()->ip(),
            'metadata' => [
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toISOString(),
            ],
        ]);
    }

    public function getConversationHistory(string $sessionId, int $limit = 10): array
    {
        return \App\Models\MCPCommandLog::where('session_id', $sessionId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->map(function ($log) {
                return [
                    'user_message' => $log->user_message,
                    'ai_response' => $log->ai_response,
                    'timestamp' => $log->created_at,
                ];
            })
            ->toArray();
    }
}
