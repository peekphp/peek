<?php

declare(strict_types=1);

namespace Peek;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Peek\Contracts\ClientInterface;

class Client implements ClientInterface
{
    private readonly GuzzleClient $httpClient;

    public function __construct(private readonly string $apiKey, private readonly string $apiUrl, private readonly string $model)
    {
        $this->httpClient = new GuzzleClient;
    }

    public function ask(string $prompt): string
    {
        try {
            $response = $this->httpClient->post($this->apiUrl.'/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $this->systemPrompt(),
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'temperature' => 0.0,
                    'stream' => false,
                ],
            ]);

            $responseBody = (string) $response->getBody();
            $responseData = json_decode($responseBody, true);

            return $responseData['choices'][0]['message']['content'] ?? 'No response from the client.';
        } catch (GuzzleException $e) {
            throw new \RuntimeException('Failed to communicate with the client: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
                You are an expert PHP developer. You are familiar with the PHP language and its ecosystem.
                You use modern PHP and the latest PHP features.
                Your task is to help the user by suggesting improvements if there are any.
                You should provide a detailed explanation of why it needs improving.
                Keep your response concise and to the point.
                Write **high-quality** and **clean** code.
            PROMPT;
    }
}
