<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MCP AI Chat Interface</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .chat-messages {
            max-height: 600px;
            overflow-y: auto;
        }
        .message {
            animation: fadeIn 0.3s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .typing {
            display: inline-block;
            animation: typing 1.5s infinite;
        }
        @keyframes typing {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-10px); }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="bg-blue-600 text-white px-6 py-4">
                <h1 class="text-2xl font-bold">🤖 MCP AI Assistant</h1>
                <p class="text-blue-100">Natural language interface for business operations</p>
            </div>

            <!-- Chat Messages -->
            <div id="chat-messages" class="chat-messages p-6 space-y-4">
                <div class="message flex items-start space-x-3">
                    <div class="bg-blue-600 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold">
                        AI
                    </div>
                    <div class="bg-blue-50 rounded-lg px-4 py-2 max-w-xs lg:max-w-md">
                        <p class="text-gray-800">Halo! Saya adalah asisten AI untuk membantu Anda mengelola data bisnis. Anda bisa meminta saya untuk membuat user baru, mencari customer, update task, dan lainnya. Apa yang bisa saya bantu hari ini?</p>
                    </div>
                </div>
            </div>

            <!-- Typing Indicator -->
            <div id="typing-indicator" class="hidden px-6 pb-4">
                <div class="flex items-center space-x-2">
                    <div class="bg-gray-400 rounded-full w-2 h-2 typing"></div>
                    <div class="bg-gray-400 rounded-full w-2 h-2 typing" style="animation-delay: 0.1s"></div>
                    <div class="bg-gray-400 rounded-full w-2 h-2 typing" style="animation-delay: 0.2s"></div>
                    <span class="text-gray-500 text-sm">AI sedang mengetik...</span>
                </div>
            </div>

            <!-- Input Form -->
            <div class="border-t px-6 py-4 bg-gray-50">
                <form id="chat-form" class="flex space-x-4">
                    <input
                        type="text"
                        id="message-input"
                        placeholder="Ketik pesan Anda... (contoh: 'Buatkan user baru dengan nama John, email john@email.com')"
                        class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        maxlength="1000"
                    >
                    <button
                        type="submit"
                        id="send-button"
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
                    >
                        Kirim
                    </button>
                </form>

                <!-- Example Messages -->
                <div class="mt-4 text-sm text-gray-600">
                    <p class="font-medium mb-2">Contoh pesan:</p>
                    <div class="flex flex-wrap gap-2">
                        <button class="example-btn bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded text-xs" data-message="Buatkan user baru dengan nama Reni, email reni@mail.com, nomor 08123456789">
                            Buat user baru
                        </button>
                        <button class="example-btn bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded text-xs" data-message="Cari semua task yang statusnya pending">
                            Cari task pending
                        </button>
                        <button class="example-btn bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded text-xs" data-message="Update status task nomor 1 menjadi completed">
                            Update task status
                        </button>
                        <button class="example-btn bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded text-xs" data-message="Tampilkan customer yang belum dihubungi lebih dari 30 hari">
                            Customer overdue
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Session Info -->
        <div class="mt-4 text-center text-sm text-gray-500">
            Session ID: <span id="session-id">Generating...</span>
        </div>
    </div>

    <script>
        let sessionId = null;
        const apiKey = '112233'; // Your MCP API key

        // Initialize session
        async function initializeSession() {
            try {
                const response = await fetch('/api/mcp/chat/history?session_id=new&limit=1', {
                    headers: {
                        'X-MCP-Key': apiKey,
                        'Content-Type': 'application/json'
                    }
                });
                const data = await response.json();
                sessionId = data.session_id || generateSessionId();
                document.getElementById('session-id').textContent = sessionId;
            } catch (error) {
                sessionId = generateSessionId();
                document.getElementById('session-id').textContent = sessionId;
            }
        }

        function generateSessionId() {
            return 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        }

        // Add message to chat
        function addMessage(content, isUser = false, metadata = null) {
            const messagesContainer = document.getElementById('chat-messages');
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message flex items-start space-x-3';

            if (isUser) {
                messageDiv.innerHTML = `
                    <div class="bg-green-600 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold ml-auto">
                        Anda
                    </div>
                    <div class="bg-green-50 rounded-lg px-4 py-2 max-w-xs lg:max-w-md ml-auto">
                        <p class="text-gray-800">${content}</p>
                    </div>
                `;
            } else {
                messageDiv.innerHTML = `
                    <div class="bg-blue-600 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold">
                        AI
                    </div>
                    <div class="bg-blue-50 rounded-lg px-4 py-2 max-w-xs lg:max-w-md">
                        <p class="text-gray-800">${content}</p>
                        ${metadata ? `<div class="mt-2 text-xs text-gray-500">${metadata}</div>` : ''}
                    </div>
                `;
            }

            messagesContainer.appendChild(messageDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        // Show/hide typing indicator
        function showTyping(show = true) {
            document.getElementById('typing-indicator').classList.toggle('hidden', !show);
        }

        // Handle form submission
        document.getElementById('chat-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            const input = document.getElementById('message-input');
            const message = input.value.trim();

            if (!message) return;

            // Add user message
            addMessage(message, true);
            input.value = '';

            // Show typing indicator
            showTyping(true);

            // Disable input
            input.disabled = true;
            document.getElementById('send-button').disabled = true;

            try {
                const response = await fetch('/api/mcp/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-MCP-Key': apiKey
                    },
                    body: JSON.stringify({
                        message: message,
                        session_id: sessionId
                    })
                });

                const data = await response.json();

                // Update session ID if provided
                if (data.session_id) {
                    sessionId = data.session_id;
                    document.getElementById('session-id').textContent = sessionId;
                }

                // Create metadata string
                let metadata = '';
                if (data.executed_tool) {
                    metadata = `🔧 Tool: ${data.executed_tool}`;
                    if (data.result && typeof data.result === 'object') {
                        if (data.result.id) {
                            metadata += ` | ID: ${data.result.id}`;
                        }
                        if (data.result.name) {
                            metadata += ` | Name: ${data.result.name}`;
                        }
                    }
                }

                // Add AI response
                addMessage(data.reply, false, metadata);

            } catch (error) {
                addMessage('Maaf, terjadi kesalahan. Silakan coba lagi.', false);
                console.error('Chat error:', error);
            } finally {
                // Hide typing indicator
                showTyping(false);

                // Re-enable input
                input.disabled = false;
                document.getElementById('send-button').disabled = false;
                input.focus();
            }
        });

        // Handle example buttons
        document.querySelectorAll('.example-btn').forEach(button => {
            button.addEventListener('click', () => {
                document.getElementById('message-input').value = button.dataset.message;
            });
        });

        // Initialize
        initializeSession();
    </script>
</body>
</html>