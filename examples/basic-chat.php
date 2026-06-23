<?php

require_once __DIR__ . '/../vendor/autoload.php';

use OpenRouteAI\OpenRouter;

// We check if the key is provided via an environment variable, otherwise fallback
$apiKey = getenv('OPENROUTER_API_KEY') ?: 'YOUR_OPENROUTER_API_KEY';

if ($apiKey === 'YOUR_OPENROUTER_API_KEY') {
    echo "Please set your OPENROUTER_API_KEY environment variable or replace 'YOUR_OPENROUTER_API_KEY' with your actual key.\n";
    exit(1);
}

$ai = new OpenRouter($apiKey);

try {
    echo "Sending chat message...\n";
    $response = $ai->chat('Hello AI');
    echo "Response:\n" . $response . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
