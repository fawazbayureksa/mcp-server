# 🤖 MCP AI Chat Server

<p align="center">
A Laravel-based AI-powered natural language interface for business operations using OpenAI's function calling capabilities.
</p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Laravel Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## 🎯 About This Project

This MCP (Model Context Protocol) AI Chat Server provides a natural language interface for business operations. Users can interact with business data using conversational language, and the AI automatically:

- Analyzes user intent from natural language
- Extracts relevant parameters
- Selects appropriate business tools
- Executes actions via RESTful APIs
- Returns human-friendly responses

Built with Laravel and powered by OpenAI's function calling, this system bridges the gap between human language and structured business operations.

## 🚀 Quick Start

### Prerequisites
- PHP 8.1+
- Composer
- Node.js & npm (for frontend assets)
- OpenAI API key
- Database (MySQL/PostgreSQL/SQLite)

### Installation

1. **Clone the repository:**
   ```bash
   git clone <repository-url>
   cd mcp-server
   ```

2. **Install dependencies:**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure environment variables:**
   ```env
   # Database
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=mcp_server
   DB_USERNAME=your_username
   DB_PASSWORD=your_password

   # OpenAI
   OPENAI_API_KEY=your-openai-api-key

   # MCP Security
   MCP_VALID_KEYS=your-mcp-api-key
   ```

5. **Run migrations:**
   ```bash
   php artisan migrate
   ```

6. **Start the server:**
   ```bash
   php artisan serve
   ```

### Access the Application

- **Chat Interface:** `http://localhost:8000/chat`
- **Documentation:** `http://localhost:8000/documentation`
- **API Documentation:** See `/documentation` for detailed API specs

## 🛠️ Available Tools

The AI can execute the following business operations:

| Tool | Description | Parameters |
|------|-------------|------------|
| `create_customer` | Create a new customer | name, email, phone |
| `get_user` | Retrieve user by email | email |
| `update_task_status` | Update task status | task_id, status |
| `search_tasks` | Search tasks by criteria | status, assigned_to |
| `get_overdue_customers` | Find customers not contacted | days (default: 30) |

## 🔒 Security Features

- **API Key Authentication** - All endpoints require X-MCP-Key header
- **Input Validation** - Messages limited to 1000 characters
- **Rate Limiting** - Built-in Laravel rate limiting
- **Comprehensive Logging** - All interactions logged in database
- **Error Handling** - Graceful responses without exposing internals

## 📊 Example Usage

```bash
# Create a new customer
curl -X POST "http://localhost:8000/api/mcp/chat" \
  -H "Content-Type: application/json" \
  -H "X-MCP-Key: your-key" \
  -d '{"message": "Buatkan customer baru nama John, email john@example.com"}'

# Update task status
curl -X POST "http://localhost:8000/api/mcp/chat" \
  -H "Content-Type: application/json" \
  -H "X-MCP-Key: your-key" \
  -d '{"message": "Update status task 1 menjadi completed"}'
```

## 🏗️ Architecture

### Backend (Laravel)
- **Framework:** Laravel 10.x
- **AI Integration:** OpenAI GPT with function calling
- **Database:** Eloquent ORM with migrations
- **API:** RESTful endpoints with JSON responses
- **Authentication:** API key-based security
- **Logging:** Comprehensive database logging

### Frontend
- **Styling:** Tailwind CSS
- **JavaScript:** Vanilla JS with fetch API
- **UI:** Responsive chat interface
- **Real-time:** Typing indicators and live updates

### AI Flow
1. **Natural Language Processing** → Intent analysis
2. **Parameter Extraction** → Structured data
3. **Tool Selection** → Function calling
4. **Execution** → Business logic
5. **Response Generation** → Human-friendly output

## 📝 API Reference

### POST `/api/mcp/chat`
Main chat endpoint for AI-powered business operations.

**Headers:**
- `Content-Type: application/json`
- `X-MCP-Key: your-api-key`

**Request Body:**
```json
{
  "message": "Create a new customer named John Doe",
  "session_id": "optional-session-identifier"
}
```

**Response:**
```json
{
  "reply": "Customer 'John Doe' created successfully with ID 42",
  "executed_tool": "create_customer",
  "result": {"id": 42, "name": "John Doe"},
  "session_id": "session-uuid"
}
```

## 🤝 Contributing

We welcome contributions! Please see our [documentation](/documentation) for detailed API specifications and development guidelines.

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
