<?php

return [
    'valid_keys' => explode(',', env('MCP_VALID_KEYS', 'your-secret-key-here')),
];