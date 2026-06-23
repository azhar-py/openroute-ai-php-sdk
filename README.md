# OpenRoute AI PHP SDK

[![Latest Version](https://img.shields.io/github/v/tag/azhar-py/openroute-ai-php-sdk?sort=semver&style=flat-square)](https://github.com/azhar-py/openroute-ai-php-sdk/tags)
[![PHP Compatibility](https://img.shields.io/badge/php-%3E%3D%207.4-8892bf.svg?style=flat-square)](https://www.php.net/releases/7_4_0.php)
[![License](https://img.shields.io/github/license/azhar-py/openroute-ai-php-sdk?style=flat-square)](LICENSE)

> [!NOTE]
> This is an **unofficial** PHP SDK for the [OpenRouter AI API](https://openrouter.ai/). It is not officially maintained, endorsed, or affiliated with OpenRouter.

A clean, light-weight, and highly reusable PHP SDK for the OpenRouter AI API. This package works in any PHP project (PHP >= 7.4) and integrates using Composer to provide immediate access to LLMs from OpenAI, Anthropic, Google, DeepSeek, Llama, and more.

---

## Key Features

- **Simple & Raw wrappers**: Access pre-formatted text outputs immediately or access full raw response headers and payloads (including token usage).
- **Custom Agent Framework**: Instantly create individual agents with custom system instructions, models, and custom behaviors.
- **Agent Orchestrator (`AgentManager`)**: Easily register, manage, and execute multiple custom agents in your workflow.
- **Robust Exception Mapping**: Captures HTTP status codes and wraps API errors into clear `OpenRouterException` instances.
- **CLI Tool Support**: Integrated shell console helper to test, ask, or list models from your terminal.

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

## Installation

To install the SDK, run the following command in your terminal:

```bash
composer require azhar-py/openroute-ai-php-sdk
```

Make sure your PHP file loads the Composer autoloader to run the classes:

```php
require_once __DIR__ . '/vendor/autoload.php';
```

---

## Detailed Setup & Usage

### 1. Basic Text Completion
To run a quick single-turn chat using the default model (`meta-llama/llama-3.3-70b-instruct`):

```php
use OpenRouteAI\OpenRouter;

// Instantiates client
$ai = new OpenRouter('YOUR_OPENROUTER_API_KEY');

// Sends prompt and prints text directly
echo $ai->chat('Explain machine learning in one sentence.');
```

### 2. Multi-turn Dialogues & System Instructions
Use the `messages` method to pass full system prompts or carry context across multiple turns:

```php
use OpenRouteAI\OpenRouter;

$ai = new OpenRouter('YOUR_OPENROUTER_API_KEY');

$conversation = [
    ['role' => 'system', 'content' => 'You speak like a medieval knight.'],
    ['role' => 'user', 'content' => 'Hello! Can you help me write a function?']
];

$reply = $ai->messages($conversation);
echo $reply;
```

### 3. Setting Up Custom Site URL & App Title
OpenRouter displays your app name on their dashboard and rankings. You can set this in the constructor:

```php
use OpenRouteAI\OpenRouter;

$ai = new OpenRouter(
    'YOUR_OPENROUTER_API_KEY',
    'meta-llama/llama-3.3-70b-instruct', // Default model
    'https://mywebsite.com',              // Site HTTP-Referer Header (Optional)
    'My Custom AI Platform'              // X-Title Header (Optional)
);
```

---

## Building and Orchestrating Multiple Agents

The SDK features a dedicated `Agent` and `AgentManager` class structure allowing you to run complex multi-agent setups.

### Single Custom Agent
You can build a standalone agent configured with a specific system role and model:

```php
use OpenRouteAI\OpenRouter;

$ai = new OpenRouter('YOUR_OPENROUTER_API_KEY');

$expertAgent = $ai->agent(
    'php_guru', 
    'meta-llama/llama-3.3-70b-instruct',
    'You are a senior PHP programmer. Explain design patterns clearly.'
);

echo $expertAgent->run('What is the strategy pattern?');
```

### Coordinating Multiple Agents with `AgentManager`
In real projects, you might need different agents to perform specialized tasks (e.g., one agent translates, another summarizes):

```php
use OpenRouteAI\OpenRouter;
use OpenRouteAI\AgentManager;

$ai = new OpenRouter('YOUR_OPENROUTER_API_KEY');
$manager = new AgentManager();

// Register a technical translator
$manager->add($ai->agent(
    'spanish_translator', 
    'meta-llama/llama-3.3-70b-instruct',
    'Translate the input text into Spanish.'
));

// Register a text refiner
$manager->add($ai->agent(
    'professional_polisher', 
    'google/gemini-2.5-flash',
    'Make the input text sound professional and business-friendly.'
));

// Run the polisher on user input
$polishedText = $manager->run('professional_polisher', 'hey client, the code is done.');

// Now run the translator on the polished text
$finalTranslation = $manager->run('spanish_translator', $polishedText);

echo $finalTranslation;
```

If you attempt to call `get()` or `run()` on an agent name that has not been registered, an `InvalidArgumentException` will be thrown.

---

## CLI Console Helper

You can query models or run completions straight from your console using the `bin/openroute` script:

```bash
# Get usage guide
php bin/openroute

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
    $ai = new OpenRouter('INVALID_API_KEY');
    $ai->chat('Hello');
} catch (OpenRouterException $e) {
    echo "Caught API Exception: " . $e->getMessage() . "\n";
    echo "HTTP Status Code: " . $e->getCode() . "\n";
}
```

---

## Full Guides & Documentation

For a detailed integration guide including:
- Decoupling the SDK into service controllers
- Managing stateful chat session history arrays in web apps
- Structuring global rate limiting and API exceptions
- Automated cron job shell integrations

Check out the [Full Integration & Usage Guide](docs/guide.md).

---

## License

This package is open-source software licensed under the [MIT License](LICENSE).
