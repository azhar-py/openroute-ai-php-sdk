<?php

namespace OpenRouteAI;

class Agent
{
    /**
     * @var OpenRouter
     */
    private $openRouter;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $model;

    /**
     * @var string
     */
    private $systemPrompt;

    /**
     * Agent constructor.
     *
     * @param OpenRouter $openRouter
     * @param string $name
     * @param string $model
     * @param string $systemPrompt
     */
    public function __construct(OpenRouter $openRouter, $name, $model, $systemPrompt)
    {
        $this->openRouter = $openRouter;
        $this->name = $name;
        $this->model = $model;
        $this->systemPrompt = $systemPrompt;
    }

    /**
     * Run the agent with the user input.
     *
     * @param string $input
     * @return string
     * @throws Exceptions\OpenRouterException
     */
    public function run($input)
    {
        $messages = [
            [
                'role' => 'system',
                'content' => $this->systemPrompt,
            ],
            [
                'role' => 'user',
                'content' => $input,
            ],
        ];

        return $this->openRouter->messages($messages, $this->model);
    }

    /**
     * Get the agent's name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the agent's model.
     *
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }
}
