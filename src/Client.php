<?php

namespace OpenRouteAI;

use GuzzleHttp\Client as GuzzleClient;
use OpenRouteAI\Exceptions\OpenRouterException;

class Client
{
    /**
     * @var GuzzleClient
     */
    private $httpClient;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string|null
     */
    private $siteUrl;

    /**
     * @var string
     */
    private $appName;

    /**
     * Client constructor.
     *
     * @param string $apiKey
     * @param string|null $siteUrl
     * @param string $appName
     * @param array $configOptions
     */
    public function __construct($apiKey, $siteUrl = null, $appName = 'OpenRoute AI PHP SDK', array $configOptions = [])
    {
        $this->apiKey = $apiKey;
        $this->siteUrl = $siteUrl;
        $this->appName = $appName;

        $headers = [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type'  => 'application/json',
        ];

        if ($this->siteUrl !== null) {
            $headers['HTTP-Referer'] = $this->siteUrl;
        }

        if ($this->appName !== null) {
            $headers['X-Title'] = $this->appName;
        }

        $defaultOptions = [
            'base_uri' => 'https://openrouter.ai/api/v1/',
            'headers'  => $headers,
        ];

        $options = array_replace_recursive($defaultOptions, $configOptions);

        $this->httpClient = new GuzzleClient($options);
    }

    /**
     * Send POST request to /chat/completions.
     *
     * @param array $payload
     * @return array
     * @throws OpenRouterException
     */
    public function chatCompletions(array $payload)
    {
        try {
            $response = $this->httpClient->post('chat/completions', [
                'json' => $payload,
            ]);

            return json_decode((string) $response->getBody(), true);
        } catch (\Exception $e) {
            throw $this->handleException($e);
        }
    }

    /**
     * Send GET request to /models.
     *
     * @return array
     * @throws OpenRouterException
     */
    public function models()
    {
        try {
            $response = $this->httpClient->get('models');

            return json_decode((string) $response->getBody(), true);
        } catch (\Exception $e) {
            throw $this->handleException($e);
        }
    }

    /**
     * Handle request exceptions and return OpenRouterException.
     *
     * @param \Exception $e
     * @return OpenRouterException
     */
    private function handleException(\Exception $e)
    {
        $message = $e->getMessage();
        $code = $e->getCode();

        if ($e instanceof \GuzzleHttp\Exception\BadResponseException) {
            $response = $e->getResponse();
            if ($response) {
                $body = (string) $response->getBody();
                $data = json_decode($body, true);
                if (isset($data['error']['message'])) {
                    $message = $data['error']['message'];
                } elseif (isset($data['error'])) {
                    $message = is_array($data['error']) ? json_encode($data['error']) : $data['error'];
                } else {
                    $message = $body;
                }
                $code = $response->getStatusCode();
            }
        }

        return new OpenRouterException($message, $code, $e);
    }
}
