const { ChatOpenAI } = require('langchain/chat_models/openai');
const { initializeAgentExecutorWithOptions } = require('langchain/agents');
const { DynamicTool } = require('langchain/tools');
const axios = require('axios');

async function createMCPTools() {
  const baseURL = 'http://localhost:8000/api';
  const headers = {
    'X-MCP-Key': 'your-secret-key-here',
    'Content-Type': 'application/json'
  };

  // Fetch tool schemas from Laravel MCP server
  const response = await axios.get(`${baseURL}/mcp/tool-schemas`, { headers });
  const toolSchemas = response.data.data;

  // Convert to LangChain tools
  const tools = toolSchemas.map(schema => {
    return new DynamicTool({
      name: schema.function.name,
      description: schema.function.description,
      func: async (input) => {
        try {
          const args = JSON.parse(input);
          const result = await axios.post(`${baseURL}/mcp/agent-call`, {
            tool: schema.function.name,
            args: args
          }, { headers });

          return JSON.stringify(result.data.result);
        } catch (error) {
          return `Error: ${error.response?.data?.error || error.message}`;
        }
      }
    });
  });

  return tools;
}

async function main() {
  // Initialize OpenAI chat model
  const model = new ChatOpenAI({
    openAIApiKey: process.env.OPENAI_API_KEY,
    modelName: 'gpt-4',
    temperature: 0
  });

  // Create MCP tools
  const tools = await createMCPTools();

  // Initialize agent executor
  const executor = await initializeAgentExecutorWithOptions(
    tools,
    model,
    {
      agentType: 'openai-functions',
      verbose: true,
      maxIterations: 5
    }
  );

  // Example query
  const result = await executor.call({
    input: 'Get information about the user with email john@example.com'
  });

  console.log(result.output);
}

main().catch(console.error);