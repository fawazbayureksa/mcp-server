import { useState, useEffect } from 'react';
import { Accordion, AccordionItem, Listbox, ListboxItem, Button, Avatar, Badge } from '@heroui/react';
import { Link, router } from '@inertiajs/react';
import axios from 'axios';
import { MessageCircle, MessageCircleDashed, MessageSquare, MessageSquarePlus, MessagesSquare } from 'lucide-react';

export default function SideBar({ agents = [] }) {
    const [sessions, setSessions] = useState([]);
    const [loading, setLoading] = useState(true);
    

    // const chatAgents = agents.length > 0 ? agents : defaultAgents;

    useEffect(() => {
        fetchSessions();
    }, []);

    const fetchSessions = async () => {
        try {
            let apiKey = import.meta.env.VITE_MCP_API_KEY;
            const response = await axios.get('/api/mcp/sessions', {
                headers: { 'X-MCP-Key': apiKey }
            });
            console.log('Sessions response:', response.data);
            // API returns data wrapped in a 'data' property
            setSessions(response.data.data || []);
        } catch (error) {
            console.error('Failed to fetch sessions:', error);
            // Fallback to empty or mock data
            setSessions([]);
        } finally {
            setLoading(false);
        }
    };

    const handleNewChat = () => {
        // Create new session and navigate
        router.visit('/chat');
    };


    return (
        <div className="w-64 dark:bg-white bg-gray-800 border-r border-gray-200 dark:border-gray-700 h-full overflow-y-auto">
            <div className="p-4">
                <h2 className="text-lg font-semibold text-gray-800 dark:text-white mb-4">Chat Menu</h2>
                <Accordion className="p-0">
                    <AccordionItem key="your-chat" aria-label="Your Chat" title="Your Chat" startContent={<MessagesSquare />}>
                        {loading ? (
                            <div className="text-sm text-gray-500">Loading sessions...</div>
                        ) : sessions.length > 0 ? (
                            <Listbox aria-label="Your Chat Sessions" className="p-0">
                                {sessions.map((session) => (
                                    <ListboxItem
                                        key={session.id}
                                        className="hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
                                        startContent={
                                           <MessageSquare className="w-5 h-5 text-gray-300 dark:text-gray-300 mr-2" />
                                        }
                                    >
                                        <Link href={`/chat?session_id=${session.id}`} className="block w-full">
                                            <div className="flex flex-col">
                                                <span className="font-medium text-gray-100 dark:text-white">
                                                    {session.name || `Session ${session.id}`}
                                                </span>
                                                <span className="text-sm text-gray-500 dark:text-gray-400">
                                                    {session.created_at ? new Date(session.created_at).toLocaleDateString() : ''}
                                                </span>
                                            </div>
                                        </Link>
                                    </ListboxItem>
                                ))}
                            </Listbox>
                        ) : (
                            <div className="text-sm text-gray-500">No sessions found</div>
                        )}
                    </AccordionItem>
                    <AccordionItem key="new-chat" aria-label="New Chat" title="New Chat" startContent={<MessageSquarePlus />}>
                        <Button
                            color="primary"
                            variant="flat"
                            onPress={handleNewChat}
                            className="w-full"
                        >
                            Start New Chat
                        </Button>
                    </AccordionItem>
                </Accordion>
            </div>
        </div>
    );
}