# MCP AI Chat Server

A Laravel-based AI-powered chat interface for business management operations using natural language processing and tool calling.

## 🎯 Overview

The MCP AI Chat Server enables users to interact with business data through natural language conversations. It leverages OpenAI's function calling capabilities to:

- Analyze user intent from natural language input
- Extract parameters automatically
- Execute appropriate business operations via MCP tools
- Return human-friendly responses

## ✨ Features

- **Natural Language Processing**: Understands Indonesian and English business queries
- **AI-Powered Tool Calling**: Uses OpenAI GPT models for intelligent function selection
- **Rule-Based Fallback**: Graceful degradation when AI services are unavailable
- **Real-time Chat Interface**: Web-based chat UI with session management
- **Comprehensive Logging**: Tracks all interactions and tool executions
- **RESTful API**: Programmatic access to chat functionality
- **Multi-Provider Support**: OpenAI and OpenRouter integration

## 🛠️ Available Tools

| Tool | Description | Parameters |
|------|-------------|------------|
| `create_customer` | Create new customer records | name, email, phone |
| `get_user` | Retrieve user by email | email |
| `search_tasks` | Find tasks by status/user | status, assigned_to |
| `update_task_status` | Change task status | task_id, status |
| `get_overdue_customers` | Find inactive customers | days |

## 🚀 Quick Start

### Prerequisites

- PHP 8.1+
- Composer
- Node.js & npm (for frontend assets)
- Database (MySQL/PostgreSQL/SQLite)

### Installation

1. **Clone and install dependencies:**
   ```bash
   git clone <repository-url>
   cd mcp-server
   composer install
   npm install
   ```

2. **Environment setup:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Configure environment variables:**
   ```env
   # Database
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=mcp_chat
   DB_USERNAME=your_username
   DB_PASSWORD=your_password

   # AI Providers
   AI_PROVIDER=openai  # or 'openrouter'
   OPENAI_API_KEY=your-openai-api-key
   OPENROUTER_API_KEY=your-openrouter-api-key

   # MCP Authentication
   MCP_VALID_KEYS=your-secret-key-here
   ```

4. **Database setup:**
   ```bash
   php artisan migrate
   php artisan db:seed  # Optional: seed sample data
   ```

5. **Build assets:**
   ```bash
   npm run build
   ```

6. **Start the server:**
   ```bash
   php artisan serve
   ```

7. **Access the application:**
   - Chat Interface: http://localhost:8000/chat
   - API Documentation: http://localhost:8000/api/documentation

## 💬 Usage Examples

### Web Interface

Visit `/chat` to use the interactive chat interface with example message buttons.

### API Usage

#### Send Chat Message
```bash
curl -X POST "http://localhost:8000/api/mcp/chat" \
  -H "Content-Type: application/json" \
  -H "X-MCP-Key: your-mcp-key" \
  -d '{
    "message": "Buatkan customer baru dengan nama John Doe, email john@example.com",
    "session_id": "optional-session-id"
  }'
```

#### Response
```json
{
  "reply": "Customer 'John Doe' berhasil dibuat dengan ID 1.",
  "executed_tool": "create_customer",
  "arguments": {
    "name": "John Doe",
    "email": "john@example.com"
  },
  "result": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  },
  "status": "success"
}
```

### Example Conversations

**Creating a customer:**
```
User: Buatkan user baru dengan nama Andi, email andi@gmail.com
AI: Customer 'Andi' berhasil dibuat dengan ID 5.
```

**Updating task status:**
```
User: Update status task nomor 1 menjadi completed
AI: Status task ID 1 berhasil diubah menjadi 'completed'.
```

**Finding overdue customers:**
```
User: Tampilkan customer yang belum dihubungi lebih dari 30 hari
AI: Ditemukan 3 customer yang belum dihubungi lebih dari 30 hari.
```

## 📡 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/mcp/chat` | Send chat message |
| GET | `/api/mcp/chat/history` | Get conversation history |
| DELETE | `/api/mcp/chat/history` | Clear conversation history |

### Authentication

All API endpoints require the `X-MCP-Key` header with a valid key from `MCP_VALID_KEYS`.

## 🔧 Configuration

### AI Providers

Choose between OpenAI and OpenRouter by setting `AI_PROVIDER`:

- **OpenAI**: More reliable, requires API key
- **OpenRouter**: Alternative provider, supports multiple models

### Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `AI_PROVIDER` | AI service provider | `openai` |
| `OPENAI_API_KEY` | OpenAI API key | - |
| `OPENROUTER_API_KEY` | OpenRouter API key | - |
| `MCP_VALID_KEYS` | Comma-separated API keys | - |
| `DB_CONNECTION` | Database connection | `mysql` |

## 🏗️ Architecture

### Core Components

- **AgentService**: Main AI processing logic
- **ToolRegistry**: Manages available MCP tools
- **MCP Tools**: Individual business operation handlers
- **ChatController**: Web interface and API endpoints
- **Logging**: Comprehensive interaction tracking

### Data Flow

1. User sends message via API or web interface
2. AgentService analyzes intent using AI or rule-based logic
3. Appropriate tool is selected and executed
4. Results are formatted into human-readable response
5. Interaction is logged for analytics

## 🔒 Security

- API key authentication for all endpoints
- Input validation and sanitization
- Rate limiting on chat endpoints
- Comprehensive error handling
- No sensitive data exposure in responses

## 📊 Monitoring

All interactions are logged in the `mcp_command_logs` table including:
- User messages and AI responses
- Executed tools and parameters
- Success/failure status
- Session tracking
- IP addresses and metadata

## 🧪 Testing

Run the test suite:
```bash
php artisan test
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🆘 Support

For issues and questions:
- Check the logs in `storage/logs/laravel.log`
- Review `mcp_command_logs` table for interaction details
- Ensure API keys are properly configured

## 📚 Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [OpenAI Function Calling](https://platform.openai.com/docs/guides/function-calling)
- [OpenRouter API](https://openrouter.ai/docs)</content>
<filePath>README.md