<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OpenAI API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for OpenAI API integration
    |
    */

    'api_key' => env('OPENAI_API_KEY'),
    
    'models' => [
        'default' => 'gpt-3.5-turbo',
        'advanced' => 'gpt-4',
    ],
    
    'max_tokens' => 4000,
    'temperature' => 0.7,
];
