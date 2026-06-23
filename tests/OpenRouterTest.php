<?php

namespace OpenRouteAI\Tests;

use PHPUnit\Framework\TestCase;
use OpenRouteAI\Client;
use OpenRouteAI\OpenRouter;
use OpenRouteAI\Agent;
use OpenRouteAI\AgentManager;
use OpenRouteAI\Exceptions\OpenRouterException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

class OpenRouterTest extends TestCase
{
    /**
     * Helper to inject a client mock/override using reflection
     */
    private function injectClient(OpenRouter $openRouter, Client $client)
    {
        $reflection = new \ReflectionClass($openRouter);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($openRouter, $client);
    }

    public function testClientSuccessChatCompletions()
    {
        $expectedResponse = [
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'Hello there!'
                    ]
                ]
            ]
        ];

        $mock = new MockHandler([
            new Response(200, [], json_encode($expectedResponse))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client('fake_key', 'site_url', 'app_name', ['handler' => $handlerStack]);

        $result = $client->chatCompletions(['messages' => []]);
        $this->assertEquals($expectedResponse, $result);
    }

    public function testClientSuccessModels()
    {
        $expectedResponse = [
            'data' => [
                ['id' => 'model-1', 'name' => 'Model One'],
                ['id' => 'model-2', 'name' => 'Model Two']
            ]
        ];

        $mock = new MockHandler([
            new Response(200, [], json_encode($expectedResponse))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client('fake_key', 'site_url', 'app_name', ['handler' => $handlerStack]);

        $result = $client->models();
        $this->assertEquals($expectedResponse, $result);
    }

    public function testClientThrowsOpenRouterException()
    {
        $errorResponse = [
            'error' => [
                'message' => 'Invalid API Key'
            ]
        ];

        $mock = new MockHandler([
            new Response(401, [], json_encode($errorResponse))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client('fake_key', null, null, ['handler' => $handlerStack]);

        $this->expectException(OpenRouterException::class);
        $this->expectExceptionMessage('Invalid API Key');
        $this->expectExceptionCode(401);

        $client->chatCompletions(['messages' => []]);
    }

    public function testOpenRouterChatAndMessages()
    {
        $apiResponse = [
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'Hello response'
                    ]
                ]
            ]
        ];

        $mock = new MockHandler([
            new Response(200, [], json_encode($apiResponse)), // First for chat
            new Response(200, [], json_encode($apiResponse))  // Second for messages
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client('fake_key', null, null, ['handler' => $handlerStack]);

        $openRouter = new OpenRouter('fake_key');
        $this->injectClient($openRouter, $client);

        // Test chat
        $chatResponse = $openRouter->chat('Hello');
        $this->assertEquals('Hello response', $chatResponse);

        // Test messages
        $messagesResponse = $openRouter->messages([
            ['role' => 'user', 'content' => 'Hello']
        ]);
        $this->assertEquals('Hello response', $messagesResponse);
    }

    public function testOpenRouterRawAndModels()
    {
        $apiResponse = [
            'id' => 'chatcmpl-123',
            'choices' => []
        ];

        $modelsResponse = [
            'data' => []
        ];

        $mock = new MockHandler([
            new Response(200, [], json_encode($apiResponse)),
            new Response(200, [], json_encode($modelsResponse))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client('fake_key', null, null, ['handler' => $handlerStack]);

        $openRouter = new OpenRouter('fake_key');
        $this->injectClient($openRouter, $client);

        $this->assertEquals($apiResponse, $openRouter->raw([]));
        $this->assertEquals($modelsResponse, $openRouter->models());
    }

    public function testAgentRun()
    {
        // Mock OpenRouter
        $openRouterMock = $this->createMock(OpenRouter::class);
        $openRouterMock->expects($this->once())
            ->method('messages')
            ->with(
                [
                    ['role' => 'system', 'content' => 'System Prompt'],
                    ['role' => 'user', 'content' => 'User Input']
                ],
                'test-model'
            )
            ->willReturn('Agent response');

        $agent = new Agent($openRouterMock, 'test_agent', 'test-model', 'System Prompt');

        $this->assertEquals('test_agent', $agent->getName());
        $this->assertEquals('test-model', $agent->getModel());
        $this->assertEquals('Agent response', $agent->run('User Input'));
    }

    public function testAgentManager()
    {
        $openRouterMock = $this->createMock(OpenRouter::class);
        $agent1 = new Agent($openRouterMock, 'agent_one', 'model-1', 'Prompt 1');
        $agent2 = new Agent($openRouterMock, 'agent_two', 'model-2', 'Prompt 2');

        $manager = new AgentManager();
        $manager->add($agent1);
        $manager->add($agent2);

        $this->assertSame($agent1, $manager->get('agent_one'));
        $this->assertSame($agent2, $manager->get('agent_two'));
        $this->assertCount(2, $manager->all());

        // Test run exception on invalid name
        $this->expectException(\InvalidArgumentException::class);
        $manager->get('non_existent');
    }
}
