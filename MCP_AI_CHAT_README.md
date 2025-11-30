# MCP AI Chat Interface - Examples & Documentation

## 🎯 System Overview

The MCP AI Chat Interface allows users to interact with business data using natural language. The system uses OpenAI's function calling to:

1. **Analyze user intent** from natural language
2. **Extract parameters** automatically
3. **Select appropriate tools** based on context
4. **Execute actions** via MCP endpoints
5. **Return human-friendly responses**

## 🔄 AI Reasoning Flow

### Example 1: Creating a New User

**User Input:** "Buatkan user baru dengan nama Andi, email andi@gmail.com dan nomor 081234"

**AI Analysis:**
```
Intent: Create new user
Parameters extracted:
- name: "Andi"
- email: "andi@gmail.com"
- phone: "081234"
Tool selected: create_customer
```

**Function Call Generated:**
```json
{
  "name": "create_customer",
  "arguments": {
    "name": "Andi",
    "email": "andi@gmail.com",
    "phone": "081234"
  }
}
```

**Tool Execution Result:**
```json
{
  "id": 6,
  "name": "Andi",
  "email": "andi@gmail.com",
  "phone": "081234"
}
```

**Human Response:** "User Andi berhasil dibuat dengan ID 6."

### Example 2: Task Status Update

**User Input:** "Tolong update status task nomor 23 menjadi completed"

**AI Analysis:**
```
Intent: Update task status
Parameters extracted:
- task_id: 23
- status: "completed"
Tool selected: update_task_status
```

**Function Call:**
```json
{
  "name": "update_task_status",
  "arguments": {
    "task_id": 23,
    "status": "completed"
  }
}
```

### Example 3: Customer Search

**User Input:** "Cari semua customer yang belum bayar lebih dari 30 hari"

**AI Analysis:**
```
Intent: Find overdue customers
Parameters extracted:
- days: 30
Tool selected: get_overdue_customers
```

## 📡 API Endpoints

### POST /api/mcp/chat
Main chat endpoint for natural language processing.

**Request:**
```json
{
  "message": "Buatkan user baru dengan nama Reni email reni@mail.com no 08123",
  "session_id": "optional-session-id"
}
```

**Response:**
```json
{
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
}
```

### GET /api/mcp/chat/history
Retrieve conversation history.

### DELETE /api/mcp/chat/history
Clear conversation history.

## 🛠️ Function Schemas

The system uses these OpenAI function schemas:

```json
[
  {
    "type": "function",
    "function": {
      "name": "create_customer",
      "description": "Create a new customer",
      "parameters": {
        "type": "object",
        "properties": {
          "name": {"type": "string", "description": "Customer name"},
          "email": {"type": "string", "description": "Customer email"},
          "phone": {"type": "string", "description": "Customer phone number"}
        },
        "required": ["name", "email"]
      }
    }
  },
  {
    "type": "function",
    "function": {
      "name": "get_user",
      "description": "Retrieve user information by email address",
      "parameters": {
        "type": "object",
        "properties": {
          "email": {"type": "string", "description": "User email address"}
        },
        "required": ["email"]
      }
    }
  },
  {
    "type": "function",
    "function": {
      "name": "update_task_status",
      "description": "Update the status of a task",
      "parameters": {
        "type": "object",
        "properties": {
          "task_id": {"type": "integer", "description": "Task ID"},
          "status": {"type": "string", "enum": ["pending", "in_progress", "completed"]}
        },
        "required": ["task_id", "status"]
      }
    }
  },
  {
    "type": "function",
    "function": {
      "name": "search_tasks",
      "description": "Search tasks by status and/or assigned user",
      "parameters": {
        "type": "object",
        "properties": {
          "status": {"type": "string", "enum": ["pending", "in_progress", "completed"]},
          "assigned_to": {"type": "integer", "description": "User ID"}
        }
      }
    }
  },
  {
    "type": "function",
    "function": {
      "name": "get_overdue_customers",
      "description": "Get customers who haven't been contacted in the specified number of days",
      "parameters": {
        "type": "object",
        "properties": {
          "days": {"type": "integer", "description": "Number of days", "default": 30}
        }
      }
    }
  }
]
```

## 🔒 Security Features

1. **API Key Authentication** - All endpoints require X-MCP-Key header
2. **Input Validation** - Messages limited to 1000 characters
3. **Rate Limiting** - Built-in Laravel rate limiting
4. **Logging** - All interactions logged in mcp_command_logs table
5. **Error Handling** - Graceful error responses without exposing internals

## 💾 Database Logging

All AI interactions are logged in the `mcp_command_logs` table:

```sql
CREATE TABLE mcp_command_logs (
    id BIGINT PRIMARY KEY,
    session_id VARCHAR(255) INDEX,
    user_message TEXT,
    ai_analysis JSON,
    detected_tool VARCHAR(255),
    extracted_args JSON,
    tool_result JSON,
    ai_response TEXT,
    status VARCHAR(255) DEFAULT 'success',
    error_message TEXT,
    ip_address VARCHAR(255),
    metadata JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## 🎨 Frontend Features

- **Real-time chat interface** with typing indicators
- **Session management** for conversation continuity
- **Example message buttons** for quick testing
- **Responsive design** using Tailwind CSS
- **Message metadata display** showing executed tools and results

## 🚀 Getting Started

1. **Set API Keys:**
   ```bash
   # In .env file
   MCP_VALID_KEYS=your-mcp-key
   OPENAI_API_KEY=your-openai-key
   ```

2. **Start Server:**
   ```bash
   php artisan serve
   ```

3. **Access Chat Interface:**
   ```
   http://localhost:8000/chat
   ```

4. **Test API Directly:**
   ```bash
   curl -X POST "http://localhost:8000/api/mcp/chat" \
     -H "Content-Type: application/json" \
     -H "X-MCP-Key: your-mcp-key" \
     -d '{"message": "Buatkan user baru dengan nama Test User, email test@example.com"}'
   ```

## 📊 Example Conversation Flow

```
User: Buatkan user baru dengan nama John Doe, email john@example.com

AI: [Analyzing intent...]
AI: [Calling create_customer tool with extracted parameters]
AI: Customer 'John Doe' berhasil dibuat dengan ID 7.
     🔧 Tool: create_customer | ID: 7 | Name: John Doe

User: Sekarang update status task nomor 1 menjadi completed

AI: [Analyzing intent...]
AI: [Calling update_task_status tool]
AI: Status task ID 1 berhasil diubah menjadi 'completed'.
     🔧 Tool: update_task_status | ID: 1 | Status: completed

User: Berapa banyak task yang masih pending?

AI: [Analyzing intent...]
AI: [Calling search_tasks tool with status=pending]
AI: Ditemukan 3 task yang sesuai dengan kriteria pencarian.
     🔧 Tool: search_tasks | Count: 3
```

This system provides a powerful natural language interface for business operations, combining AI reasoning with structured tool execution.