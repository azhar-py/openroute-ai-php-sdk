<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OpenRouter API Key
    |--------------------------------------------------------------------------
    |
    | Your OpenRouter API token. You can generate one at https://openrouter.ai/keys
    |
    */
    'api_key' => getenv('OPENROUTER_API_KEY') ?: '',

    /*
    |--------------------------------------------------------------------------
    | Default LLM Model
    |--------------------------------------------------------------------------
    |
    | The default model used when none is explicitly requested.
    |
    */
    'default_model' => getenv('OPENROUTER_DEFAULT_MODEL') ?: 'meta-llama/llama-3.3-70b-instruct',

    /*
    |--------------------------------------------------------------------------
    | App and Site Metadata (Optional)
    |--------------------------------------------------------------------------
    |
    | Setting these values enables OpenRouter to include your app name in
    | their rankings and properly log referrals.
    |
    */
    'site_url' => getenv('SITE_URL') ?: null,
    'app_name' => getenv('APP_NAME') ?: 'OpenRoute AI PHP SDK',

    /*
    |--------------------------------------------------------------------------
    | Pre-Configured Agents
    |--------------------------------------------------------------------------
    |
    | Define custom agents here. They will be auto-loaded into the AgentManager
    | when OpenRouter is instantiated without custom arguments.
    |
    */
    'agents' => [
        'summarizer' => [
            'model' => 'meta-llama/llama-3.3-70b-instruct',
            'system_prompt' => 'You are an editor. Summarize the user input into a single concise sentence.',
        ],
        'translator' => [
            'model' => 'meta-llama/llama-3.3-70b-instruct',
            'system_prompt' => 'You are a professional translator. Translate all user input to Spanish. Output ONLY the translation.',
        ]
    ]
];
