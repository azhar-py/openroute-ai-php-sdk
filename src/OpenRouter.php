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
     * @var AgentManager
     */
    private $agentManager;

    /**
     * OpenRouter constructor.
     *
     * @param string|null $apiKey
     * @param string|null $defaultModel
     * @param string|null $siteUrl
     * @param string|null $appName
     * @throws Exceptions\OpenRouterException
     */
    public function __construct($apiKey = null, $defaultModel = null, $siteUrl = null, $appName = null)
    {
        $config = $this->loadConfig();

        $apiKey = $apiKey ?: (isset($config['api_key']) ? $config['api_key'] : null);
        $defaultModel = $defaultModel ?: (isset($config['default_model']) ? $config['default_model'] : 'meta-llama/llama-3.3-70b-instruct');
        $siteUrl = $siteUrl ?: (isset($config['site_url']) ? $config['site_url'] : null);
        $appName = $appName ?: (isset($config['app_name']) ? $config['app_name'] : 'OpenRoute AI PHP SDK');

        if (!$apiKey) {
            throw new Exceptions\OpenRouterException("API Key is required. Set it in the constructor, environment, or in config/openroute.php");
        }

        $this->client = new Client($apiKey, $siteUrl, $appName);
        $this->defaultModel = $defaultModel;
        $this->agentManager = new AgentManager();

        // Auto-load config agents
        if (isset($config['agents']) && is_array($config['agents'])) {
            foreach ($config['agents'] as $name => $agentConfig) {
                if (isset($agentConfig['system_prompt'])) {
                    $aModel = isset($agentConfig['model']) ? $agentConfig['model'] : $this->defaultModel;
                    $this->agentManager->add(new Agent($this, $name, $aModel, $agentConfig['system_prompt']));
                }
            }
        }
    }

    /**
     * Get the auto-configured AgentManager.
     *
     * @return AgentManager
     */
    public function agents()
    {
        return $this->agentManager;
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

    /**
     * Load the SDK configuration.
     *
     * @return array
     */
    private function loadConfig()
    {
        $searchPaths = [
            dirname(__DIR__, 4) . '/config/openroute.php', // relative to vendor
            getcwd() . '/config/openroute.php',            // current working directory
            dirname(__DIR__, 1) . '/config/openroute.php', // local development inside package
        ];

        foreach ($searchPaths as $path) {
            if (file_exists($path)) {
                $config = include $path;
                if (is_array($config)) {
                    return $config;
                }
            }
        }

        return [];
    }
}
