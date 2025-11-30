import os
import json
import requests
from langchain.agents import initialize_agent, AgentType
from langchain.chat_models import ChatOpenAI
from langchain.tools import Tool

class MCPTool:
    def __init__(self, name, description, base_url, api_key):
        self.name = name
        self.description = description
        self.base_url = base_url
        self.api_key = api_key
        self.headers = {
            'X-MCP-Key': api_key,
            'Content-Type': 'application/json'
        }

    def run(self, args_str):
        try:
            args = json.loads(args_str)
            response = requests.post(
                f"{self.base_url}/mcp/agent-call",
                json={
                    "tool": self.name,
                    "args": args
                },
                headers=self.headers
            )
            response.raise_for_status()
            return json.dumps(response.json()['result'])
        except Exception as e:
            return f"Error: {str(e)}"

def create_mcp_tools(base_url, api_key):
    # Fetch tool schemas
    response = requests.get(f"{base_url}/mcp/tool-schemas",
                          headers={'X-MCP-Key': api_key})
    tool_schemas = response.json()['data']

    tools = []
    for schema in tool_schemas:
        func_def = schema['function']
        tool = MCPTool(
            name=func_def['name'],
            description=func_def['description'],
            base_url=base_url,
            api_key=api_key
        )

        langchain_tool = Tool(
            name=func_def['name'],
            description=func_def['description'],
            func=tool.run
        )
        tools.append(langchain_tool)

    return tools

def main():
    # Configuration
    base_url = "http://localhost:8000/api"
    api_key = "your-secret-key-here"
    openai_api_key = os.getenv("OPENAI_API_KEY")

    # Initialize OpenAI model
    llm = ChatOpenAI(
        openai_api_key=openai_api_key,
        model="gpt-4",
        temperature=0
    )

    # Create MCP tools
    tools = create_mcp_tools(base_url, api_key)

    # Initialize agent
    agent = initialize_agent(
        tools=tools,
        llm=llm,
        agent=AgentType.OPENAI_FUNCTIONS,
        verbose=True,
        max_iterations=5
    )

    # Example query
    result = agent.run("Find all pending tasks assigned to user ID 1")
    print(result)

if __name__ == "__main__":
    main()