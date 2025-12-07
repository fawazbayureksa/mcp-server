import { useState, useEffect, useRef } from 'react';
import axios from 'axios';
import { Button, Input } from '@heroui/react';
import { Send, Sparkles } from 'lucide-react';
import SideBar from '../Layouts/SideBar';
import { Head } from '@inertiajs/react';

export default function Chat({ SessionId }) {
    const [messages, setMessages] = useState([
        {
            id: 1,
            content: 'Halo, saya adalah AI Assistant. Anda dapat meminta saya untuk membuat user, mencari data, update status task, dan automasi lainnya. Apa yang ingin Anda lakukan hari ini?',
            isUser: false,
            metadata: null
        }
    ]);
    const [inputValue, setInputValue] = useState('');
    const [isTyping, setIsTyping] = useState(false);
    const [sessionId, setSessionId] = useState(SessionId || null);
    const messagesEndRef = useRef(null);

    const apiKey = '112233';

    useEffect(() => {
        if (sessionId) {
            initializeSession();
        } else {
            setSessionId(generateSessionId());
        }
    }, []);

    useEffect(() => {
        scrollToBottom();
    }, [messages]);


    const initializeSession = async () => {
        try {
            const response = await axios.get('/api/mcp/chat/history', {
                params: { session_id: sessionId, limit: 50 },
                headers: { 'X-MCP-Key': apiKey }
            });
            
            console.log('Chat history response:', response.data);
            
            if (response.data.success && response.data.history && response.data.history.length > 0) {
                // Clear default message and load history
                const historyMessages = [];
                
                response.data.history.forEach((msg, index) => {
                    // Add user message
                    if (msg.user_message) {
                        historyMessages.push({
                            id: `user-${Date.now()}-${index}`,
                            content: msg.user_message,
                            isUser: true,
                            metadata: null
                        });
                    }
                    
                    // Add AI response
                    if (msg.ai_response) {
                        historyMessages.push({
                            id: `ai-${Date.now()}-${index}`,
                            content: msg.ai_response,
                            isUser: false,
                            metadata: null
                        });
                    }
                });
                
                setMessages(historyMessages);
            }
        } catch (error) {
            console.error('Failed to load chat history:', error);
            // Keep default welcome message
        }
    };

    const generateSessionId = () => {
        return 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    };

    const addMessage = (content, isUser = false, metadata = null) => {
        const newMessage = {
            id: Date.now(),
            content,
            isUser,
            metadata
        };
        setMessages(prev => [...prev, newMessage]);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        const message = inputValue.trim();
        if (!message) return;

        addMessage(message, true);
        setInputValue('');
        setIsTyping(true);

        try {
            const response = await axios.post('/api/mcp/chat', {
                message,
                session_id: sessionId
            }, {
                headers: { 'X-MCP-Key': apiKey }
            });

            const data = response.data;
            if (data.session_id) {
                setSessionId(data.session_id);
            }

            const metadata = data.executed_tool ?
                `Tool: ${data.executed_tool}${data.result?.id ? ` | ID: ${data.result.id}` : ''}` :
                '';
            addMessage(data.reply, false, metadata);
        } catch {
            addMessage('An error occurred. Please try again.', false);
        } finally {
            setIsTyping(false);
        }
    };

    const handleExampleClick = (message) => {
        setInputValue(message);
    };

    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    };

    const exampleMessages = [
        'Buat user baru dengan nama Reni, email reni@mail.com, nomor 08123456789',
        'Cari semua task yang statusnya pending',
        'Update status task nomor 1 menjadi completed',
        'Tampilkan customer yang belum dihubungi lebih dari 30 hari'
    ];

    return (
        <div className="flex h-screen bg-gradient-to-br from-[#1a1a2e] via-[#16213e] to-[#0f1419] text-white">
            <Head title="Chat" />
            <SideBar />
            <div className="flex-1 flex flex-col relative">
                {/* Animated background effect */}
                <div className="absolute inset-0 overflow-hidden pointer-events-none">
                    <div className="absolute -top-1/2 -right-1/2 w-full h-full bg-gradient-radial from-blue-500/10 via-transparent to-transparent rounded-full blur-3xl animate-pulse"></div>
                    <div className="absolute -bottom-1/2 -left-1/2 w-full h-full bg-gradient-radial from-purple-500/10 via-transparent to-transparent rounded-full blur-3xl animate-pulse" style={{ animationDelay: '1s' }}></div>
                </div>

                {/* Header */}
                <div className="relative border-b border-white/10 backdrop-blur-sm bg-white/5 p-6">
                    <div className="flex items-center gap-3">
                        <div className="relative">
                            <div className="w-10 h-10 rounded-full bg-gradient-to-br from-blue-300 to-purple-400 flex items-center justify-center">
                                <Sparkles className="w-5 h-5 text-white" />
                            </div>
                            <div className="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-[#16213e] animate-pulse"></div>
                        </div>
                        <div>
                            <h1 className="text-2xl font-bold bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">
                                Your AI Assistant
                            </h1>
                            <p className="text-gray-400 text-sm">Help you with heart</p>
                        </div>
                    </div>
                </div>

                {/* Messages */}
                <div className="relative flex-1 overflow-y-auto p-6">
                    <div className="max-w-4xl mx-auto space-y-6">
                        {messages.map((msg) => (
                            <div key={msg.id} className={`flex ${msg.isUser ? 'justify-end' : 'justify-start'} opacity-0 animate-[fadeIn_0.3s_ease-in_forwards]`}>
                                <div className={`max-w-2xl ${
                                    msg.isUser 
                                        ? 'bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-lg shadow-blue-500/20' 
                                        : 'bg-white/10 backdrop-blur-md text-gray-100 border border-white/10 shadow-xl'
                                } rounded-2xl px-5 py-4 transform transition-all hover:scale-[1.02]`}>
                                    <p className="whitespace-pre-wrap leading-relaxed">{msg.content}</p>
                                    {msg.metadata && (
                                        <div className="mt-3 text-xs text-gray-300 border-t border-white/10 pt-3 flex items-center gap-2">
                                            <Sparkles className="w-3 h-3" />
                                            {msg.metadata}
                                        </div>
                                    )}
                                </div>
                            </div>
                        ))}
                        {isTyping && (
                            <div className="flex justify-start">
                                <div className="bg-white/10 backdrop-blur-md text-gray-100 rounded-2xl px-5 py-4 shadow-xl border border-white/10 max-w-2xl">
                                    <div className="flex space-x-2 items-center">
                                        <div className="w-2.5 h-2.5 bg-blue-400 rounded-full animate-bounce"></div>
                                        <div className="w-2.5 h-2.5 bg-purple-400 rounded-full animate-bounce" style={{ animationDelay: '0.15s' }}></div>
                                        <div className="w-2.5 h-2.5 bg-pink-400 rounded-full animate-bounce" style={{ animationDelay: '0.3s' }}></div>
                                        <span className="text-sm text-gray-400 ml-2">AI is thinking...</span>
                                    </div>
                                </div>
                            </div>
                        )}
                        <div ref={messagesEndRef} />
                    </div>
                </div>

                {/* Input */}
                <div className="relative border-t border-white/10 backdrop-blur-xl bg-gradient-to-b from-[#16213e]/80 to-[#0f1419]/80 p-6">
                    <div className="max-w-4xl mx-auto">
                        {/* Example Messages */}
                        <div className="mb-4 flex flex-wrap gap-2">
                            {exampleMessages.map((msg, index) => (
                                <button
                                    key={index}
                                    onClick={() => handleExampleClick(msg)}
                                    className="group relative px-4 py-2 rounded-xl bg-white/5 border border-white/10 hover:border-blue-400/50 hover:bg-white/10 text-gray-300 hover:text-white text-xs transition-all duration-300 overflow-hidden"
                                >
                                    <div className="absolute inset-0 bg-gradient-to-r from-blue-500/20 to-purple-500/20 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                    <span className="relative z-10 flex items-center gap-2">
                                        <Sparkles className="w-3 h-3 opacity-70 group-hover:opacity-100" />
                                        {['Create New User', 'Find Pending Tasks', 'Update Task Status', 'Customer Not Contacted'][index]}
                                    </span>
                                </button>
                            ))}
                        </div>

                        <form onSubmit={handleSubmit} className="relative">
                            <div className="relative group">
                                <div className="absolute -inset-1 bg-gradient-to-r from-blue-600 via-purple-300 to-pink-300 rounded-2xl opacity-20 group-hover:opacity-30 blur transition-opacity"></div>
                                <div className="relative flex items-center gap-3 bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-2 shadow-2xl">
                                    <input
                                        type="text"
                                        placeholder="Type your message here..."
                                        value={inputValue}
                                        onChange={(e) => setInputValue(e.target.value)}
                                        maxLength={1000}
                                        disabled={isTyping}
                                        className="flex-1 bg-transparent border-none outline-none text-white placeholder-gray-400 px-4 py-3 text-sm"
                                    />
                                    <button
                                        type="submit"
                                        disabled={isTyping || !inputValue.trim()}
                                        className="relative group/btn px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-900 hover:from-blue-500 hover:to-purple-500 text-white rounded-xl font-medium disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300 shadow-lg hover:shadow-blue-500/50 disabled:hover:shadow-none flex items-center gap-2"
                                    >
                                        <span className="relative z-10">Send</span>
                                        <Send className="w-4 h-4 relative z-10 group-hover/btn:translate-x-0.5 transition-transform" />
                                        <div className="absolute inset-0 bg-white/20 rounded-xl opacity-0 group-hover/btn:opacity-100 transition-opacity"></div>
                                    </button>
                                </div>
                            </div>
                        </form>

                        <p className="text-center text-xs text-gray-500 mt-3">
                            AI can make mistakes. Please verify important information.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
}