# OpenRouter Integration Guide

## 🚀 Setting Up OpenRouter

### 1. Get OpenRouter API Key
1. Visit [OpenRouter.ai](https://openrouter.ai/)
2. Sign up for an account
3. Go to [API Keys](https://openrouter.ai/keys)
4. Create a new API key
5. Copy the key

### 2. Configure Your Laravel App

Update your `.env` file:

```bash
# Set AI provider to OpenRouter
AI_PROVIDER=openrouter

# Add your OpenRouter API key
OPENROUTER_API_KEY=sk-or-v1-xxxxxxxxxxxxx

# Keep OpenAI key as backup (optional)
OPENAI_API_KEY=your-openai-key-here
```

### 3. Choose Your AI Model

OpenRouter supports many models. Popular options:

- `anthropic/claude-3-opus` - Most powerful
- `anthropic/claude-3-sonnet` - Balanced
- `anthropic/claude-3-haiku` - Fast & cheap
- `openai/gpt-4-turbo` - GPT-4 Turbo
- `openai/gpt-3.5-turbo` - Fast & reliable

Update the model in `AgentService.php`:

```php
'model' => 'anthropic/claude-3-haiku', // Change this to your preferred model
```

## 🧪 Testing OpenRouter Integration

### Test Basic Chat
```bash
curl -X POST "http://localhost:8000/api/mcp/chat" \
  -H "Content-Type: application/json" \
  -H "X-MCP-Key: 112233" \
  -d '{"message": "halo, siapa kamu?"}'
```

### Test Function Calling
```bash
curl -X POST "http://localhost:8000/api/mcp/chat" \
  -H "Content-Type: application/json" \
  -H "X-MCP-Key: 112233" \
  -d '{"message": "Buatkan customer baru dengan nama Budi dari OpenRouter email budi@openrouter.com"}'
```

## 🔄 Switching Between Providers

### Use OpenRouter
```bash
AI_PROVIDER=openrouter
OPENROUTER_API_KEY=your-key
```

### Use OpenAI
```bash
AI_PROVIDER=openai
OPENAI_API_KEY=your-key
```

### Use Rule-Based Only
```bash
AI_PROVIDER=none
```

## 💰 OpenRouter Pricing

OpenRouter offers very competitive pricing:

- **Claude 3 Haiku**: ~$0.00025 per token
- **GPT-4 Turbo**: ~$0.001 per token
- **Claude 3 Opus**: ~$0.0025 per token

Much cheaper than direct OpenAI/Claude APIs!

## 🎯 Supported Models

```php
// Popular models you can use:
'anthropic/claude-3-opus'      // Most capable
'anthropic/claude-3-sonnet'    // Balanced
'anthropic/claude-3-haiku'     // Fast & cheap
'openai/gpt-4-turbo'          // GPT-4 Turbo
'openai/gpt-3.5-turbo'        // Reliable
'google/gemini-pro'           // Google's model
'mistralai/mistral-7b-instruct' // Open source
```

## 🔧 Advanced Configuration

### Custom Model Selection
```php
private function callOpenRouter(array $messages): array
{
    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $this->openRouterApiKey,
        'Content-Type' => 'application/json',
    ])->post('https://openrouter.ai/api/v1/chat/completions', [
        'model' => env('OPENROUTER_MODEL', 'anthropic/claude-3-haiku'),
        'messages' => $messages,
        'tools' => $this->functionSchemas,
        'tool_choice' => 'auto',
        'temperature' => env('AI_TEMPERATURE', 0.1),
        'max_tokens' => env('AI_MAX_TOKENS', 1000),
    ]);

    // ... rest of the method
}
```

Add to `.env`:
```bash
OPENROUTER_MODEL=anthropic/claude-3-sonnet
AI_TEMPERATURE=0.1
AI_MAX_TOKENS=1000
```

## 🚨 Troubleshooting

### "Invalid API key" Error
- Check your OpenRouter API key is correct
- Ensure it's not expired

### "Model not found" Error
- Verify the model name is correct
- Check if you have access to that model

### Function Calling Not Working
- Some models may not support function calling well
- Try Claude models - they excel at function calling
- Fall back to rule-based AI if needed

### Rate Limiting
- OpenRouter has rate limits
- Implement retry logic if needed

## 🎉 Benefits of OpenRouter

1. **Cost Effective**: Much cheaper than direct APIs
2. **Model Variety**: Access to 100+ models
3. **Unified API**: Same interface for all models
4. **No Quota Issues**: Pay-as-you-go pricing
5. **High Reliability**: Distributed infrastructure

Your MCP AI Chat is now powered by OpenRouter! 🚀