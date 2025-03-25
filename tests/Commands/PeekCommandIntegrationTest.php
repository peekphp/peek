<?php

use Peek\Client;
use Peek\Commands\PeekCommand;
use Symfony\Component\Console\Tester\CommandTester;

beforeEach(function (): void {
    // Define the path to the peek.json file
    $configPath = __DIR__.'/../../peek.json';

    // Get API key from environment
    $apiKey = getenv('PEEK_API_KEY');

    if (empty($apiKey)) {
        $this->markTestSkipped('PEEK_API_KEY environment variable not set. Skipping integration tests.');

        return;
    }

    // Create configuration with the API key
    $defaultClient = [
        'api_key' => $apiKey,
        'url' => 'https://api.deepseek.com',
        'model' => 'deepseek-model',
    ];

    $config = [
        'clients' => [
            'deepseek' => $defaultClient,
        ],
    ];

    file_put_contents($configPath, json_encode($config, JSON_PRETTY_PRINT));
    $this->config = $config;
});

afterEach(function (): void {
    // Clean up the config file after tests
    $configPath = __DIR__.'/../../peek.json';
    if (file_exists($configPath)) {
        unlink($configPath);
    }
});

it('fails when no API key is present', function (): void {
    new Client('', 'https://api.deepseek.com', 'deepseek-model');
    $command = new PeekCommand;
    $commandTester = new CommandTester($command);

    $filePath = 'tests/Fixtures/FilesToAnalyse/ClassWithErrors.php';

    $commandTester->execute(['file' => $filePath]);
    $output = $commandTester->getDisplay();

    expect($output)
        ->toContain('Failed to communicate with the client:')
        ->toContain('401 Unauthorized');
})->skip('Valid API key not found in peek.json. Skipping test.');

it('fails with an invalid API key', function (): void {
    new Client('invalid-key', 'https://api.deepseek.com', 'deepseek-model');
    $command = new PeekCommand;
    $commandTester = new CommandTester($command);

    $filePath = 'tests/Fixtures/FilesToAnalyse/ClassWithErrors.php';

    $commandTester->execute(['file' => $filePath]);
    $output = $commandTester->getDisplay();

    expect($output)
        ->toContain('Failed to communicate with the client:')
        ->toContain('401 Unauthorized');
})->skip('Valid API key not found in peek.json. Skipping test.');

it('succeeds with a valid API key', function (): void {
    $clients = $this->config['clients'] ?? [];
    $validClient = reset($clients);

    $client = new Client($validClient['api_key'], $validClient['url'], $validClient['model']);
    $command = new PeekCommand($client);
    $commandTester = new CommandTester($command);

    $filePath = 'tests/Fixtures/FilesToAnalyse/SmallClassWithErrors.php';

    $commandTester->execute(['file' => $filePath]);
    $output = $commandTester->getDisplay();

    expect($output)
        ->toContain('Analyzing the entire file:')
        ->toContain('Analysis Result:')
        ->not->toContain('401 Unauthorized')
        ->not->toContain('Authentication Fails');
});
