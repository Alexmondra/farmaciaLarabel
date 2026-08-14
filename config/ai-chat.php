<?php

return [
    'api_key' => env('AI_CHAT_API_KEY', ''),
    'provider' => env('AI_CHAT_PROVIDER', 'deepseek'),
    'model' => env('AI_CHAT_MODEL', 'deepseek-chat'),
    'base_url' => env('AI_CHAT_BASE_URL', 'https://api.deepseek.com/v1'),
    'temperature' => 0.7,
    'max_tokens' => 1500,
    'max_history_messages' => 20,
];
