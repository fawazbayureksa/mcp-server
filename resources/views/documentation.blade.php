<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MCP AI Chat Documentation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .nav-link {
            transition: all 0.3s ease;
        }
        .nav-link:hover {
            transform: translateY(-1px);
        }
        pre {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 0.375rem;
            padding: 1rem;
            overflow-x: auto;
        }
        code {
            background-color: #f1f3f4;
            padding: 0.125rem 0.25rem;
            border-radius: 0.25rem;
            font-size: 0.875em;
        }
        .section {
            margin-bottom: 2rem;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <h1 class="text-xl font-bold text-gray-800">MCP Server</h1>
                    </div>
                    <div class="hidden md:block ml-10">
                        <div class="flex items-baseline space-x-4">
                            <a href="/" class="nav-link text-gray-600 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">Home</a>
                            <a href="/chat" class="nav-link text-gray-600 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">Chat Interface</a>
                            <a href="/documentation" class="nav-link bg-blue-600 text-white px-3 py-2 rounded-md text-sm font-medium">Documentation</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">🤖 MCP AI Chat Interface - Documentation</h1>

            <div class="section">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">🎯 System Overview</h2>
                <p class="text-gray-600 mb-4">
                    The MCP AI Chat Interface allows users to interact with business data using natural language. The system uses OpenAI's function calling to:
                </p>
                <ul class="list-disc list-inside text-gray-600 mb-4">
                    <li><strong>Analyze user intent</strong> from natural language</li>
                    <li><strong>Extract parameters</strong> automatically</li>
                    <li><strong>Select appropriate tools</strong> based on context</li>
                    <li><strong>Execute actions</strong> via MCP endpoints</li>
                    <li><strong>Return human-friendly responses</strong></li>
                </ul>
            </div>

            <div class="section">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">🔄 AI Reasoning Flow</h2>
                <h3 class="text-xl font-medium text-gray-700 mb-2">Example 1: Creating a New User</h3>
                <p class="text-gray-600 mb-2"><strong>User Input:</strong> "Buatkan user baru dengan nama Andi, email andi@gmail.com dan nomor 081234"</p>

                <p class="text-gray-600 mb-2"><strong>AI Analysis:</strong></p>
                <pre><code>Intent: Create new user
Parameters extracted:
- name: "Andi"
- email: "andi@gmail.com"
- phone: "081234"
Tool selected: create_customer</code></pre>

                <p class="text-gray-600 mb-2"><strong>Function Call Generated:</strong></p>
                <pre><code>{
  "name": "create_customer",
  "arguments": {
    "name": "Andi",
    "email": "andi@gmail.com",
    "phone": "081234"
  }
}</code></pre>

                <p class="text-gray-600 mb-2"><strong>Human Response:</strong> "User Andi berhasil dibuat dengan ID 6."</p>
            </div>

            <div class="section">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">📡 API Endpoints</h2>

                <h3 class="text-xl font-medium text-gray-700 mb-2">POST /api/mcp/chat</h3>
                <p class="text-gray-600 mb-2">Main chat endpoint for natural language processing.</p>

                <p class="text-gray-600 mb-2"><strong>Request:</strong></p>
                <pre><code>{
  "message": "Buatkan user baru dengan nama Reni email reni@mail.com no 08123",
  "session_id": "optional-session-id"
}</code></pre>

                <p class="text-gray-600 mb-2"><strong>Response:</strong></p>
                <pre><code>{
  "reply": "Customer 'Reni' berhasil dibuat dengan ID 34",
  "executed_tool": "create_customer",
  "arguments": {
    "name": "Reni",
    "email": "reni@mail.com",
    "phone": "08123"
  },
  "result": {
    "id": 34,
    "name": "Reni",
    "email": "reni@mail.com",
    "phone": "08123"
  },
  "status": "success",
  "session_id": "session-uuid"
}</code></pre>
            </div>

            <div class="section">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">🛠️ Available Tools</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-blue-800">create_customer</h4>
                        <p class="text-sm text-blue-600">Create a new customer with name, email, and phone</p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-green-800">get_user</h4>
                        <p class="text-sm text-green-600">Retrieve user information by email address</p>
                    </div>
                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-yellow-800">update_task_status</h4>
                        <p class="text-sm text-yellow-600">Update the status of a task (pending/in_progress/completed)</p>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-purple-800">search_tasks</h4>
                        <p class="text-sm text-purple-600">Search tasks by status and/or assigned user</p>
                    </div>
                    <div class="bg-red-50 p-4 rounded-lg md:col-span-2">
                        <h4 class="font-semibold text-red-800">get_overdue_customers</h4>
                        <p class="text-sm text-red-600">Get customers who haven't been contacted in the specified number of days</p>
                    </div>
                </div>
            </div>

            <div class="section">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">🔒 Security Features</h2>
                <ul class="list-disc list-inside text-gray-600">
                    <li><strong>API Key Authentication</strong> - All endpoints require X-MCP-Key header</li>
                    <li><strong>Input Validation</strong> - Messages limited to 1000 characters</li>
                    <li><strong>Rate Limiting</strong> - Built-in Laravel rate limiting</li>
                    <li><strong>Logging</strong> - All interactions logged in mcp_command_logs table</li>
                    <li><strong>Error Handling</strong> - Graceful error responses without exposing internals</li>
                </ul>
            </div>

            <div class="section">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">🚀 Getting Started</h2>

                <h3 class="text-xl font-medium text-gray-700 mb-2">1. Set API Keys:</h3>
                <pre><code># In .env file
MCP_VALID_KEYS=your-mcp-key
OPENAI_API_KEY=your-openai-key</code></pre>

                <h3 class="text-xl font-medium text-gray-700 mb-2">2. Start Server:</h3>
                <pre><code>php artisan serve</code></pre>

                <h3 class="text-xl font-medium text-gray-700 mb-2">3. Access Interfaces:</h3>
                <ul class="list-disc list-inside text-gray-600 mb-4">
                    <li><strong>Chat Interface:</strong> <code>http://localhost:8000/chat</code></li>
                    <li><strong>Documentation:</strong> <code>http://localhost:8000/documentation</code></li>
                </ul>

                <h3 class="text-xl font-medium text-gray-700 mb-2">4. Test API Directly:</h3>
                <pre><code>curl -X POST "http://localhost:8000/api/mcp/chat" \
  -H "Content-Type: application/json" \
  -H "X-MCP-Key: your-mcp-key" \
  -d '{"message": "Buatkan user baru dengan nama Test User, email test@example.com"}'</code></pre>
            </div>

            <div class="section">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">📊 Example Conversation Flow</h2>
                <pre><code>User: Buatkan user baru dengan nama John Doe, email john@example.com

AI: Customer 'John Doe' berhasil dibuat dengan ID 7.
     🔧 Tool: create_customer | ID: 7 | Name: John Doe

User: Sekarang update status task nomor 1 menjadi completed

AI: Status task ID 1 berhasil diubah menjadi 'completed'.
     🔧 Tool: update_task_status | ID: 1 | Status: completed

User: Berapa banyak task yang masih pending?

AI: Ditemukan 3 task yang sesuai dengan kriteria pencarian.
     🔧 Tool: search_tasks | Count: 3</code></pre>
            </div>

            <div class="mt-8 p-4 bg-gray-100 rounded-lg">
                <p class="text-gray-600 text-center">
                    This system provides a powerful natural language interface for business operations,
                    combining AI reasoning with structured tool execution.
                </p>
            </div>
        </div>
    </div>
</body>
</html>