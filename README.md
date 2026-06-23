# OpenRoute AI PHP SDK

A simple, clean, and reusable PHP SDK for the OpenRouter AI API. This package works in any PHP project (PHP >= 7.4) and integrates seamlessly using Composer.

## Installation

Install the package via Composer:

```bash
composer require azhar-py/openroute-ai-php-sdk
```

Ensure your project loads the Composer autoloader:

```php
require_once __DIR__ . '/vendor/autoload.php';
```

---

## Basic Usage

To quick-start a chat with the default model (`meta-llama/llama-3.3-70b-instruct`):

```php
use OpenRouteAI\OpenRouter;

$ai = new OpenRouter('YOUR_OPENROUTER_API_KEY');

// Send a simple message and get the assistant's response text directly
echo $ai->chat('Hello AI');
```

---

## Custom Model Usage

You can specify a different model or customize other client settings:

```php
use OpenRouteAI\OpenRouter;

$ai = new OpenRouter(
    'YOUR_OPENROUTER_API_KEY', 
    'google/gemini-2.5-flash' // Default model
);

// Run a chat using a specific model override
echo $ai->chat('Explain PHP autoloading', 'deepseek/deepseek-chat');
```

---

## OpenAI-Style Messages

For multi-turn chats or system prompt configurations, use the `messages` method:

```php
use OpenRouteAI\OpenRouter;

$ai = new OpenRouter('YOUR_OPENROUTER_API_KEY');

$messages = [
    ['role' => 'system', 'content' => 'You are a poetic assistant.'],
    ['role' => 'user', 'content' => 'Write a short poem about PHP.']
];

$response = $ai->messages($messages);
echo $response;
```

---

## Raw API Response

If you need access to the full API response (including token usage, finish reasons, etc.):

```php
use OpenRouteAI\OpenRouter;

$ai = new OpenRouter('YOUR_OPENROUTER_API_KEY');

$payload = [
    'model' => 'meta-llama/llama-3.3-70b-instruct',
    'messages' => [
        ['role' => 'user', 'content' => 'What is 2+2?']
    ],
    'temperature' => 0.5
];

$rawResponse = $ai->raw($payload);
print_r($rawResponse);
```

---

## Agent Usage

You can create an independent `Agent` that holds its own name, model, and system prompt:

```php
use OpenRouteAI\OpenRouter;

$ai = new OpenRouter('YOUR_OPENROUTER_API_KEY');

$agent = $ai->agent(
    'php_expert',
    'meta-llama/llama-3.3-70b-instruct',
    'You are a senior PHP developer. Explain things simply.'
);

echo $agent->run('Explain Composer autoloading');
```

---

## AgentManager Usage

To manage multiple agents in your application, use the `AgentManager`:

```php
use OpenRouteAI\OpenRouter;
use OpenRouteAI\AgentManager;

$ai = new OpenRouter('YOUR_OPENROUTER_API_KEY');
$manager = new AgentManager();

// Create and register agents
$manager->add($ai->agent(
    'writer', 
    'meta-llama/llama-3.3-70b-instruct', 
    'You are a creative writer.'
));

$manager->add($ai->agent(
    'coder', 
    'meta-llama/llama-3.3-70b-instruct', 
    'You are a software engineer.'
));

// Run a registered agent by name
echo $manager->run('writer', 'Write a sentence about rain.');
```

---

## Models List

Fetch all available models on OpenRouter:

```php
use OpenRouteAI\OpenRouter;

$ai = new OpenRouter('YOUR_OPENROUTER_API_KEY');

$models = $ai->models();
print_r($models);
```

---

## CLI Usage

The package includes a convenient command-line interface for quick testing.

```bash
# Ask the AI using the default model
php bin/openroute --key=YOUR_OPENROUTER_API_KEY --ask="Hello AI"

# Ask the AI using a specific model
php bin/openroute --key=YOUR_OPENROUTER_API_KEY --model="google/gemini-2.5-flash" --ask="Explain quantum physics"

# List available models
php bin/openroute --key=YOUR_OPENROUTER_API_KEY --models
```

---

## Error Handling

All HTTP client and API error responses are captured and thrown as an `OpenRouterException`:

```php
use OpenRouteAI\OpenRouter;
use OpenRouteAI\Exceptions\OpenRouterException;

try {
    $ai = new OpenRouter('INVALID_KEY');
    $ai->chat('Hello');
} catch (OpenRouterException $e) {
    echo "API Error: " . $e->getMessage() . " (Code: " . $e->getCode() . ")\n";
}
```

---

## License

This project is licensed under the MIT License. See [LICENSE](LICENSE) for details.
