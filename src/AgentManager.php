<?php

namespace OpenRouteAI;

use InvalidArgumentException;

class AgentManager
{
    /**
     * @var array
     */
    private $agents = [];

    /**
     * Add an agent to the manager.
     *
     * @param Agent $agent
     * @return void
     */
    public function add(Agent $agent)
    {
        $this->agents[$agent->getName()] = $agent;
    }

    /**
     * Retrieve an agent by name.
     *
     * @param string $name
     * @return Agent
     * @throws InvalidArgumentException
     */
    public function get($name)
    {
        if (!isset($this->agents[$name])) {
            throw new InvalidArgumentException(sprintf('Agent with name "%s" does not exist.', $name));
        }

        return $this->agents[$name];
    }

    /**
     * Run an agent by name with the given input.
     *
     * @param string $name
     * @param string $input
     * @return string
     * @throws InvalidArgumentException
     * @throws Exceptions\OpenRouterException
     */
    public function run($name, $input)
    {
        return $this->get($name)->run($input);
    }

    /**
     * Get all registered agents.
     *
     * @return array
     */
    public function all()
    {
        return $this->agents;
    }
}
