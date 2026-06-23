# OpenRoute AI PHP SDK

[![Latest Version](https://img.shields.io/github/v/tag/azhar-py/openroute-ai-php-sdk?sort=semver&style=flat-square)](https://github.com/azhar-py/openroute-ai-php-sdk/tags)
[![PHP Compatibility](https://img.shields.io/badge/php-%3E%3D%207.4-8892bf.svg?style=flat-square)](https://www.php.net/releases/7_4_0.php)
[![License](https://img.shields.io/github/license/azhar-py/openroute-ai-php-sdk?style=flat-square)](LICENSE)

> [!NOTE]
> This is an **unofficial** PHP SDK for the [OpenRouter AI API](https://openrouter.ai/). It is not officially maintained, endorsed, or affiliated with OpenRouter.

A clean, light-weight, and highly reusable PHP SDK for the OpenRouter AI API. This package works in any PHP project (PHP >= 7.4) and integrates using Composer to provide immediate access to LLMs from OpenAI, Anthropic, Google, DeepSeek, Llama, and more.

---

## Getting Started with OpenRouter

To use this SDK, you will need an API key from OpenRouter:

1. **Create an Account**: Visit [https://openrouter.ai/](https://openrouter.ai/) and click **Login** or **Sign Up**. You can authenticate using OAuth providers (Google, GitHub, MetaMask, etc.) or email.
2. **Deposit Credits (Optional)**: While OpenRouter offers many free models (marked with `:free` in their model IDs), standard models require account credits. Go to your **Billing** dashboard to add funds as needed.
3. **Generate an API Key**:
   - Go to the **Keys** section under your user profile menu or navigate directly to [https://openrouter.ai/keys](https://openrouter.ai/keys).
   - Click **Create Key**.
   - Copy your key and save it securely (e.g., in a `.env` file). You will not be able to view it again.

---

## Key Features

- **Configuration File Publishing**: Generate a unified settings file in your project (`config/openroute.php`) for seamless initialization.
- **Zero-Setup Instantiation**: Automatically auto-loads configurations and pre-defined agents from your project's settings.
- **Simple & Raw wrappers**: Access pre-formatted text outputs immediately or access full raw response headers and payloads (including token usage).
- **Custom Agent Framework**: Instantly create individual agents with custom system instructions, models, and custom behaviors.
- **Agent Orchestrator (`AgentManager`)**: Easily register, manage, and execute multiple custom agents in your workflow.
- **Robust Exception Mapping**: Captures HTTP status codes and wraps API errors into clear `OpenRouterException` instances.
- **CLI Tool Support**: Integrated shell console helper to initialize configurations, test prompts, or list models from your terminal.

---

## Installation & Setup

### 1. Require the Package via Composer
To install the SDK, run the following command in your terminal:

```bash
composer require azhar-py/openroute-ai-php-sdk
```

### 2. Publish the Configuration File
Generate the configuration file inside your project root to manage settings:

```bash
php vendor/bin/openroute init
```

This creates a file under `config/openroute.php` preconfigured to load settings from environment variables.

---

## Detailed Setup & Usage

### 1. Basic Text Completion
To run a quick single-turn chat using the default model:

```php
require_once __DIR__ . '/vendor/autoload.php';

use OpenRouteAI\OpenRouter;

// Instantiates client - automatically loads key and settings from config/openroute.php
$ai = new OpenRouter();

// Sends prompt and prints text directly
echo $ai->chat('Explain machine learning in one sentence.');
```

### 2. Custom Model Overrides
You can specify a different model or customize settings inline:

```php
use OpenRouteAI\OpenRouter;

$ai = new OpenRouter();

// Run a chat using a specific model override
echo $ai->chat('Explain PHP autoloading', 'deepseek/deepseek-chat');
```

### 3. Multi-turn Dialogues & System Instructions
Use the `messages` method to pass full system prompts or carry context across multiple turns:

```php
use OpenRouteAI\OpenRouter;

$ai = new OpenRouter();

$conversation = [
    ['role' => 'system', 'content' => 'You speak like a medieval knight.'],
    ['role' => 'user', 'content' => 'Hello! Can you help me write a function?']
];

$reply = $ai->messages($conversation);
echo $reply;
```

---

## Building and Orchestrating Multiple Agents

The SDK features a dedicated `Agent` and `AgentManager` class structure allowing you to run complex multi-agent setups.

### Auto-Loaded Config Agents
You can define preconfigured agents in `config/openroute.php`:

```php
return [
    'agents' => [
        'summarizer' => [
            'model' => 'meta-llama/llama-3.3-70b-instruct',
            'system_prompt' => 'You are an editor. Summarize the user input into a single concise sentence.',
        ],
        'translator' => [
            'model' => 'meta-llama/llama-3.3-70b-instruct',
            'system_prompt' => 'Translate all user input to Spanish.',
        ]
    ]
];
```

You can then run them directly without any extra code setup:

```php
use OpenRouteAI\OpenRouter;

$ai = new OpenRouter();

// Run the summarizer agent defined in config
$summary = $ai->agents()->run('summarizer', 'Long text to summarize...');
echo $summary;
```

### Programmatic Custom Agents
You can also build standalone agents dynamically:

```php
use OpenRouteAI\OpenRouter;

$ai = new OpenRouter();

$expertAgent = $ai->agent(
    'php_guru', 
    'meta-llama/llama-3.3-70b-instruct',
    'You are a senior PHP programmer. Explain design patterns clearly.'
);

echo $expertAgent->run('What is the strategy pattern?');
```

---

## RAG (Retrieval-Augmented Generation) & Knowledge Search

You can implement grounded RAG search flows by retrieving document context and injecting it into the prompts:

```php
use OpenRouteAI\OpenRouter;

$ai = new OpenRouter();

// 1. Retrieve knowledge text from database/search
$knowledgeText = "The OpenRoute AI PHP SDK package was tagged with release v1.0.0 on June 23, 2026.";

// 2. Format system instruction with context
$messages = [
    [
        'role' => 'system',
        'content' => "Use the following context to answer query:\n\n" . $knowledgeText
    ],
    [
        'role' => 'user',
        'content' => "What is the release date of the package?"
    ]
];

// 3. Generate answer
echo $ai->messages($messages);
```

---

## CLI Console Helper

You can query models, run completions, or publish configs straight from your terminal:

```bash
# Publish config/openroute.php template
php bin/openroute init

# Ask a prompt using the default model
php bin/openroute --key=YOUR_API_KEY --ask="Hello AI"

# Ask a prompt using a specific model override
php bin/openroute --key=YOUR_API_KEY --model="google/gemini-2.5-flash" --ask="Write a tag line"

# Retrieve all available models list
php bin/openroute --key=YOUR_API_KEY --models
```

---

## Production Error Handling

All request failures, timeout conditions, and authentication/invalid key responses from the API are captured and thrown as an `OpenRouterException`:

```php
use OpenRouteAI\OpenRouter;
use OpenRouteAI\Exceptions\OpenRouterException;

try {
    $ai = new OpenRouter();
    $ai->chat('Hello');
} catch (OpenRouterException $e) {
    echo "Caught API Exception: " . $e->getMessage() . "\n";
    echo "HTTP Status Code: " . $e->getCode() . "\n";
}
```

---

## Full Guides & Documentation

For a detailed integration guide including MVC service structures, stateful chat history in web sessions, and global rate limiting wrappers, check out the [Full Integration & Usage Guide](docs/guide.md).

---

## License

This package is open-source software licensed under the [MIT License](LICENSE).
