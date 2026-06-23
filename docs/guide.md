# OpenRoute AI PHP SDK - Full Integration & Usage Guide

Welcome to the comprehensive guide for the **OpenRoute AI PHP SDK** (`azhar-py/openroute-ai-php-sdk`). This guide walks you through integrating this SDK into real-world applications, building complex AI agent workflows, and structuring your project professionally.

---

## Table of Contents
1. [Installation & Config Publishing](#1-installation--config-publishing)
2. [Configuration Auto-loading](#2-configuration-auto-loading)
3. [Building and Orchestrating Multiple Agents](#3-building-and-orchestrating-multiple-agents)
4. [Real-World Project Integration](#4-real-world-project-integration)
   - [Configuring Environment Variables](#configuring-environment-variables)
   - [Creating a Controller / Service Structure](#creating-a-controller--service-structure)
   - [Managing Context and Chat Session History](#managing-context-and-chat-session-history)
   - [Global Error & Exception Handling](#global-error--exception-handling)
5. [Designing RAG (Retrieval-Augmented Generation) & Knowledge Search](#5-designing-rag-retrieval-augmented-generation--knowledge-search)
6. [CLI Integration in Production](#6-cli-integration-in-production)

---

## 1. Installation & Config Publishing

Before using the SDK, make sure your PHP environment (PHP >= 7.4) has Composer installed.

Run the following command to require the package in your project:

```bash
composer require azhar-py/openroute-ai-php-sdk
```

### Publishing the Configuration File

The SDK supports zero-setup instantiation by publishing a configuration file directly to your project's `config/` directory.

Run the initialization command from your project root:

```bash
php vendor/bin/openroute init
```

This creates a new folder/file under `config/openroute.php` in your project if it doesn't already exist.

---

## 2. Configuration Auto-loading

Once published, you can instantiate the main `OpenRouter` wrapper without parameters. The SDK automatically searches for `config/openroute.php` to resolve variables:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use OpenRouteAI\OpenRouter;

// Auto-loads API Key, default model, and pre-configured agents from config/openroute.php
$ai = new OpenRouter();

try {
    // 1. Run a standard chat
    echo $ai->chat('Hello AI');

    // 2. Access auto-loaded agents directly from the manager
    $summary = $ai->agents()->run('summarizer', 'Long article text here...');
    echo "Summary: " . $summary;
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

---

## 3. Building and Orchestrating Multiple Agents

The SDK supports creating customized agents that have their own personality (system prompts) and models. Using the `AgentManager`, you can load, register, and run multiple agents in the same session.

### Declaring Agents in Config (`config/openroute.php`)

You can define agents directly in the config file, which will be auto-loaded upon instantiation:

```php
return [
    'api_key' => getenv('OPENROUTER_API_KEY'),
    'agents' => [
        'editor' => [
            'model' => 'meta-llama/llama-3.3-70b-instruct',
            'system_prompt' => 'You are an editor. Check the user input for spelling errors.'
        ],
        'translator' => [
            'model' => 'google/gemini-2.5-flash',
            'system_prompt' => 'Translate the input to French.'
        ]
    ]
];
```

### Code Example: Multi-Agent Workflow

Here is how to register and run multiple agents:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use OpenRouteAI\OpenRouter;

$ai = new OpenRouter(); // Auto-loads config

// Run Translator
$frenchTranslation = $ai->agents()->run('translator', 'Hello, how are you?');
echo $frenchTranslation;
```

---

## 4. Real-World Project Integration

When building a production-ready application (such as a Symfony, Laravel, or custom MVC project), you should decouple the SDK calls into dedicated services.

### Configuring Environment Variables

Never hardcode your API key in the source code. Install a package like `vlucas/phpdotenv` or use system-level env variables.

Create a `.env` file at the project root:

```env
OPENROUTER_API_KEY=sk-or-v1-xxxxxxxxxxxxxxxxxxxxxxxx
OPENROUTER_DEFAULT_MODEL=meta-llama/llama-3.3-70b-instruct
SITE_URL=https://my-app.com
APP_NAME="My AI Product Platform"
```

### Creating a Controller / Service Structure

Create a service wrapper class to inject the SDK client dependency injection-style.

```php
<?php

namespace App\Services;

use OpenRouteAI\OpenRouter;
use OpenRouteAI\Exceptions\OpenRouterException;

class AIService
{
    private $openRouter;

    public function __construct()
    {
        // Instantiates automatically picking up environment and config settings
        $this->openRouter = new OpenRouter();
    }

    public function getAiResponse(string $prompt): string
    {
        try {
            return $this->openRouter->chat($prompt);
        } catch (OpenRouterException $e) {
            // Log error or raise custom domain exception
            error_log('AI Service Error: ' . $e->getMessage());
            throw new \RuntimeException('AI Service is temporarily unavailable.');
        }
    }
}
```

### Managing Context and Chat Session History

Chatbots require memory of previous turns. Since API requests are stateless, you must store and pass the message history array. Here's a session-based approach:

```php
<?php

session_start();

require_once __DIR__ . '/vendor/autoload.php';

use OpenRouteAI\OpenRouter;

$ai = new OpenRouter();

// Initialize chat history in session
if (!isset($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = [
        ['role' => 'system', 'content' => 'You are a helpful customer support agent.']
    ];
}

// Handle User Message submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    $userMessage = trim($_POST['message']);
    
    // Add user message to history
    $_SESSION['chat_history'][] = ['role' => 'user', 'content' => $userMessage];
    
    try {
        // Send complete history to OpenRouter
        $assistantReply = $ai->messages($_SESSION['chat_history']);
        
        // Save assistant reply to history
        $_SESSION['chat_history'][] = ['role' => 'assistant', 'content' => $assistantReply];
        
    } catch (\Exception $e) {
        $error = "Error communicating with AI: " . $e->getMessage();
    }
}
```

### Global Error & Exception Handling

The SDK catches bad status codes and wraps them in `OpenRouterException`. In your project's global exception handler or middleware, map this appropriately:

```php
<?php

use OpenRouteAI\Exceptions\OpenRouterException;

try {
    $response = $ai->chat('Hello');
} catch (OpenRouterException $e) {
    $statusCode = $e->getCode();
    
    switch ($statusCode) {
        case 401:
            // Alert administration of an expired/invalid API key
            notify_admin_invalid_key();
            break;
        case 429:
            // Show rate-limit warning to client
            http_response_code(429);
            echo json_encode(['error' => 'Too many requests. Please try again later.']);
            exit;
        case 503:
            // Handle upstream service outages
            http_response_code(503);
            echo json_encode(['error' => 'Upstream AI model is currently offline.']);
            exit;
        default:
            // General exception fallback
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error occurred.']);
            exit;
    }
}
```

---

## 5. Designing RAG (Retrieval-Augmented Generation) & Knowledge Search

Retrieval-Augmented Generation (RAG) grounds LLM outputs in verified external knowledge. Here is how you construct a search-augmented pipeline:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use OpenRouteAI\OpenRouter;

// 1. Instantiation
$ai = new OpenRouter();

// 2. Perform your document search (vector DB, ElasticSearch, or web search)
$userQuery = "What is the release date of the package?";
$retrievedDocumentText = "The OpenRoute AI PHP SDK package was tagged with release v1.0.0 on June 23, 2026.";

// 3. Formulate RAG context payload
$messages = [
    [
        'role' => 'system',
        'content' => "You are a factual assistant. Answer queries using the following context only:\n\n" . $retrievedDocumentText
    ],
    [
        'role' => 'user',
        'content' => $userQuery
    ]
];

// 4. Generate answer
$response = $ai->messages($messages);
echo $response; // "The package was tagged with release v1.0.0 on June 23, 2026."
```

---

## 6. CLI Integration in Production

You can call the SDK command-line interface in server cron jobs or background bash processes.

For example, to run automated nightly translations, checkups, or content generation:

```bash
# Run CLI script as a cron task redirecting output to a log file
0 2 * * * php /var/www/project/bin/openroute --ask="Generate site analytics summary report" >> /var/log/ai_nightly.log 2>&1
```
