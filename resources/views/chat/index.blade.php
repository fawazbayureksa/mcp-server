<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
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
            from {
                opacity: 0;
                transform: translateY(6px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .typing {
            animation: typing 1.2s infinite;
        }

        @keyframes typing {

            0%,
            60%,
            100% {
                opacity: .5;
            }

            30% {
                opacity: 1;
            }
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">

            <!-- Header -->
            <div class="bg-gray-900 text-white px-6 py-5">
                <h1 class="text-xl font-semibold tracking-wide">AI Assistant</h1>
                <p class="text-gray-300 text-sm leading-tight mt-1">Conversational Interface for Operational Intelligence
                </p>
            </div>

            <!-- Chat Messages -->
            <div id="chat-messages" class="chat-messages p-6 space-y-5 bg-gray-50">
                <div class="message flex items-start space-x-3">
                    <div class="bg-gray-900 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm">
                        AI
                    </div>
                    <div class="bg-white border border-gray-200 rounded-lg px-4 py-3 max-w-lg shadow-sm">
                        <p class="text-gray-800 text-sm leading-relaxed">
                            Halo, saya adalah AI Assistant. Anda dapat meminta saya untuk membuat user, mencari
                            data,
                            update status task, dan automasi lainnya. Apa yang ingin Anda lakukan hari ini?
                        </p>
                    </div>
                </div>
            </div>

            <!-- Typing Indicator -->
            <div id="typing-indicator" class="hidden px-6 pb-4">
                <div class="flex items-center space-x-2 text-gray-500 text-sm">
                    <div class="w-2 h-2 rounded-full bg-gray-400 typing"></div>
                    <div class="w-2 h-2 rounded-full bg-gray-400 typing"></div>
                    <div class="w-2 h-2 rounded-full bg-gray-400 typing"></div>
                    <span>AI is preparing a response...</span>
                </div>
            </div>

            <!-- Input Form -->
            <div class="border-t px-6 py-4 bg-white">
                <form id="chat-form" class="flex space-x-4">
                    <input type="text" id="message-input" placeholder="Type your request..."
                        class="flex-1 border border-gray-300 rounded-md px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-700"
                        maxlength="1000">
                    <button type="submit" id="send-button"
                        class="bg-gray-900 text-white px-6 py-2 text-sm rounded-md hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-700 disabled:opacity-50">
                        Send
                    </button>
                </form>

                <!-- Example Messages -->
                <div class="mt-4 text-sm text-gray-600">
                    <p class="font-medium mb-2">Try these commands:</p>
                    <div class="flex flex-wrap gap-2">
                        <button class="example-btn bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded text-xs"
                            data-message="Buat user baru dengan nama Reni, email reni@mail.com, nomor 08123456789">
                            Create New User
                        </button>
                        <button class="example-btn bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded text-xs"
                            data-message="Cari semua task yang statusnya pending">
                            Find Pending Tasks
                        </button>
                        <button class="example-btn bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded text-xs"
                            data-message="Update status task nomor 1 menjadi completed">
                            Update Task Status
                        </button>
                        <button class="example-btn bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded text-xs"
                            data-message="Tampilkan customer yang belum dihubungi lebih dari 30 hari">
                            Customer Not Contacted
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Session Info -->
        <div class="mt-4 text-center text-xs text-gray-500">
            Session ID: <span id="session-id">Initializing...</span>
        </div>
    </div>

    <script>
        let sessionId = null;
        const apiKey = '112233';

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
            } catch {
                sessionId = generateSessionId();
            }
            document.getElementById('session-id').textContent = sessionId;
        }

        function generateSessionId() {
            return 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        }

        function addMessage(content, isUser = false, metadata = null) {
            const messagesContainer = document.getElementById('chat-messages');
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message flex items-start space-x-3';

            if (isUser) {
                messageDiv.innerHTML = `
          <div class="bg-gray-700 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm ml-auto">Me</div>
          <div class="bg-white border border-gray-200 rounded-lg px-4 py-3 max-w-lg ml-auto shadow-sm">
            <p class="text-gray-800 text-sm">${content}</p>
          </div>`;
            } else {
                messageDiv.innerHTML = `
          <div class="bg-gray-900 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm">AI</div>
          <div class="bg-white border border-gray-200 rounded-lg px-4 py-3 max-w-lg shadow-sm">
            <p class="text-gray-800 text-sm">${content}</p>
            ${metadata ? `<div class="mt-2 text-xs text-gray-500">${metadata}</div>` : ''}
          </div>`;
            }

            messagesContainer.appendChild(messageDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        function showTyping(show = true) {
            document.getElementById('typing-indicator').classList.toggle('hidden', !show);
        }

        document.getElementById('chat-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const input = document.getElementById('message-input');
            const message = input.value.trim();
            if (!message) return;

            addMessage(message, true);
            input.value = '';
            showTyping(true);

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
                        message,
                        session_id: sessionId
                    })
                });

                const data = await response.json();
                if (data.session_id) {
                    sessionId = data.session_id;
                    document.getElementById('session-id').textContent = sessionId;
                }

                const metadata = data.executed_tool ?
                    `Tool: ${data.executed_tool}${data.result?.id ? ` | ID: ${data.result.id}` : ''}` :
                    '';
                console.log(metadata);
                addMessage(data.reply, false, metadata);
            } catch {
                addMessage('An error occurred. Please try again.', false);
            } finally {
                showTyping(false);
                input.disabled = false;
                document.getElementById('send-button').disabled = false;
                input.focus();
            }
        });

        document.querySelectorAll('.example-btn').forEach(button => {
            button.addEventListener('click', () => {
                document.getElementById('message-input').value = button.dataset.message;
            });
        });

        initializeSession();
    </script>
</body>

</html>
