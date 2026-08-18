<?php

return [
    'api_key' => env('AI_CHAT_API_KEY', ''),
    'provider' => env('AI_CHAT_PROVIDER', 'deepseek'),
    'model' => env('AI_CHAT_MODEL', 'deepseek-chat'),
    'base_url' => env('AI_CHAT_BASE_URL', 'https://api.deepseek.com/v1'),
    'temperature' => 0.7,
    'max_tokens' => 1500,
    'max_history_messages' => 20,

    // Configuración específica para el cliente (FarmaBot - DeepSeek)
    'client' => [
        'api_key' => env('AI_CLIENT_API_KEY', env('AI_CHAT_API_KEY', '')),
        'model' => env('AI_CLIENT_MODEL', env('AI_CHAT_MODEL', 'deepseek-chat')),
        'base_url' => env('AI_CLIENT_BASE_URL', env('AI_CHAT_BASE_URL', 'https://api.deepseek.com/v1')),
        'temperature' => env('AI_CLIENT_TEMPERATURE', 0.7),
        'max_tokens' => env('AI_CLIENT_MAX_TOKENS', 1500),
    ],

    // Configuración específica para el personal (FarmaCopiloto - Gemini)
    'personal' => [
        'api_key' => env('AI_PERSONAL_API_KEY', ''),
        'model' => env('AI_PERSONAL_MODEL', 'gemini-1.5-flash'),
        'base_url' => env('AI_PERSONAL_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta/openai'),
        'temperature' => env('AI_PERSONAL_TEMPERATURE', 0.2),
        'max_tokens' => env('AI_PERSONAL_MAX_TOKENS', 2048),
    ],
];
