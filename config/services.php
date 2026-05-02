<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'ai_invoice' => [
        'ocr_url' => env('OCR_SERVICE_URL', 'http://127.0.0.1:5000'),
        'ollama_url' => env('OLLAMA_URL', 'http://127.0.0.1:11434/api/generate'),
        'ollama_model' => env('OLLAMA_MODEL', 'llama3'),
        // OCR + Ollama HTTP timeouts can exceed PHP's default max_execution_time (60).
        'max_execution_seconds' => (int) env('AI_INVOICE_MAX_EXECUTION', 360),
        // Our business (buyer). Never use these as party_name — that field is the seller who issued the bill.
        'buyer_aliases' => array_values(array_filter(array_map('trim', explode(',', (string) env(
            'AI_INVOICE_BUYER_ALIASES',
            'Sandip Oil Depo,Sandeep Oil Depo,SANDIP OIL DEPO,SANDEEP OIL DEPO,M/s SANDEEP OIL DEPO,M/s SANDIP OIL DEPO,Sandip Oil Depo General Store'
        ))))),
    ],

];
