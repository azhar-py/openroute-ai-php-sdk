<?php

namespace OpenRouteAI;

class OpenRouter
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $defaultModel;

    /**
     * OpenRouter constructor.
     *
     * @param string $apiKey
     * @param string $defaultModel
     * @param string|null $siteUrl
     * @param string $appName
     */
    public function __construct($apiKey, $defaultModel = 'meta-llama/llama-3.3-70b-instruct', $siteUrl = null, $appName = 'OpenRoute AI PHP SDK')
    {
        $this->client = new Client($apiKey, $siteUrl, $appName);
        $this->defaultModel = $defaultModel;
    }

    /**
     * Send a simple string message and get the assistant's response content.
     *
     * @param string $message
     * @param string|null $model
     * @return string
     * @throws Exceptions\OpenRouterException
     */
    public function chat($message, $model = null)
    {
        $messages = [
            [
                'role' => 'user',
                'content' => $message,
            ],
        ];

        return $this->messages($messages, $model);
    }

    /**
     * Send an OpenAI-style messages array and get the assistant's response content.
     *
     * @param array $messages
     * @param string|null $model
     * @return string
     * @throws Exceptions\OpenRouterException
     */
    public function messages(array $messages, $model = null)
    {
        $payload = [
            'model' => $model ?? $this->defaultModel,
            'messages' => $messages,
        ];

        $response = $this->raw($payload);

        return isset($response['choices'][0]['message']['content'])
            ? $response['choices'][0]['message']['content']
            : '';
    }

    /**
     * Send a raw payload to the chat/completions endpoint.
     *
     * @param array $payload
     * @return array
     * @throws Exceptions\OpenRouterException
     */
    public function raw(array $payload)
    {
        return $this->client->chatCompletions($payload);
    }

    /**
     * Get the list of available models from OpenRouter.
     *
     * @return array
     * @throws Exceptions\OpenRouterException
     */
    public function models()
    {
        return $this->client->models();
    }

    /**
     * Get a new Agent instance.
     *
     * @param string $name
     * @param string $model
     * @param string $systemPrompt
     * @return Agent
     */
    public function agent($name, $model, $systemPrompt)
    {
        return new Agent($this, $name, $model, $systemPrompt);
    }
}
