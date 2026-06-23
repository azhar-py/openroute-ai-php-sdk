# OpenRoute AI PHP SDK - Full Integration & Usage Guide

Welcome to the comprehensive guide for the **OpenRoute AI PHP SDK** (`azhar-py/openroute-ai-php-sdk`). This guide walks you through integrating this SDK into real-world applications, building complex AI agent workflows, and structuring your project professionally.

---

## Table of Contents
1. [Installation & Setup](#1-installation--setup)
2. [Building and Orchestrating Multiple Agents](#2-building-and-orchestrating-multiple-agents)
3. [Real-World Project Integration](#3-real-world-project-integration)
   - [Configuring Environment Variables](#configuring-environment-variables)
   - [Creating a Controller / Service Structure](#creating-a-controller--service-structure)
   - [Managing Context and Chat Session History](#managing-context-and-chat-session-history)
   - [Global Error & Exception Handling](#global-error--exception-handling)
4. [CLI Integration in Production](#4-cli-integration-in-production)

---

## 1. Installation & Setup

Before using the SDK, make sure your PHP environment (PHP >= 7.4) has Composer installed.

Run the following command to require the package in your project:

```bash
composer require azhar-py/openroute-ai-php-sdk
```

This will automatically download Guzzle and map the namespace `OpenRouteAI\` to your project's vendor directory.

### Quick Verification

Create an `index.php` at the root of your project:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use OpenRouteAI\OpenRouter;

// Retrieve your key securely
$apiKey = getenv('OPENROUTER_API_KEY') ?: 'your_api_key';

$ai = new OpenRouter($apiKey);

try {
    echo $ai->chat('Say: SDK is ready!');
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

---

## 2. Building and Orchestrating Multiple Agents

The SDK supports creating customized agents that have their own personality (system prompts) and models. Using the `AgentManager`, you can load, register, and run multiple agents in the same session.

### Why use multiple agents?
In production applications, a single system prompt is often insufficient. Separating responsibilities into dedicated agents (e.g., Code Reviewer, Content Writer, Translator) improves accuracy and reduces token consumption.

### Code Example: Multi-Agent Workflow

Here is how to register and run multiple agents:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use OpenRouteAI\OpenRouter;
use OpenRouteAI\AgentManager;

$ai = new OpenRouter(getenv('OPENROUTER_API_KEY'));
$manager = new AgentManager();

// 1. Create a Translator Agent
$translator = $ai->agent(
    'translator',
    'meta-llama/llama-3.3-70b-instruct',
    'You are a professional translator. Translate all user input to Spanish. Output ONLY the translation.'
);

// 2. Create a Summary Agent
$summarizer = $ai->agent(
    'summarizer',
    'meta-llama/llama-3.3-70b-instruct',
    'You are an editor. Summarize the user input into a single concise sentence.'
);

// Register agents to the manager
$manager->add($translator);
$manager->add($summarizer);

// 3. Coordinate the execution
$article = "Composer is a tool for dependency management in PHP. It allows you to declare the libraries your project depends on and it will manage (install/update) them for you.";

// Run Summarizer first
$summary = $manager->run('summarizer', $article);
echo "Summary: " . $summary . "\n\n";

// Translate the summary
$translation = $manager->run('translator', $summary);
echo "Spanish Translation of Summary: " . $translation . "\n";
```

---

## 3. Real-World Project Integration

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
    private $defaultModel;

    public function __construct()
    {
        $apiKey = getenv('OPENROUTER_API_KEY');
        $this->defaultModel = getenv('OPENROUTER_DEFAULT_MODEL') ?: 'meta-llama/llama-3.3-70b-instruct';
        $siteUrl = getenv('SITE_URL');
        $appName = getenv('APP_NAME') ?: 'My Application';

        if (!$apiKey) {
            throw new \RuntimeException('OPENROUTER_API_KEY is not defined in the environment.');
        }

        $this->openRouter = new OpenRouter($apiKey, $this->defaultModel, $siteUrl, $appName);
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

$apiKey = getenv('OPENROUTER_API_KEY');
$ai = new OpenRouter($apiKey);

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

## 4. CLI Integration in Production

You can call the SDK command-line interface in server cron jobs or background bash processes.

For example, to run automated nightly translations, checkups, or content generation:

```bash
# Run CLI script as a cron task redirecting output to a log file
0 2 * * * php /var/www/project/bin/openroute --key="sk-or-v1-..." --ask="Generate site analytics summary report" >> /var/log/ai_nightly.log 2>&1
```
