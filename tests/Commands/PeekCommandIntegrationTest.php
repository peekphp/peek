<?php

use Peek\Client;
use Peek\Commands\PeekCommand;
use Symfony\Component\Console\Tester\CommandTester;

beforeEach(function (): void {
    // Define the path to the peek.json file
    $configPath = __DIR__.'/../../peek.json';

    // Define a variable key (this can be modified dynamically)
    $defaultClient = [
        'api_key' => getenv('PEEK_API_KEY') ?: 'valid-key', // Use an env variable or fallback to a default
        'url' => 'https://api.deepseek.com',
        'model' => 'deepseek-model',
    ];

    if (! file_exists($configPath)) {
        // Create a new peek.json file with the default client
        $config = [
            'clients' => [
                'deepseek' => $defaultClient,
            ]
        ];

        file_put_contents($configPath, json_encode($config, JSON_PRETTY_PRINT));
        $this->config = $config;
    } else {
        // Read and decode the existing file
        $this->config = json_decode(file_get_contents($configPath), true);
    }

    // Validate JSON
    if (json_last_error() !== JSON_ERROR_NONE) {
        $this->markTestSkipped('peek.json contains invalid JSON. Skipping integration tests.');
    }
});

it('fails when no API key is present', function (): void {
    new Client('', 'https://api.deepseek.com', 'deepseek-model');
    $command = new PeekCommand();
    $commandTester = new CommandTester($command);

    $filePath = 'tests/Fixtures/FilesToAnalyse/ClassWithErrors.php';

    $commandTester->execute(['file' => $filePath]);
    $output = $commandTester->getDisplay();

    expect($output)
        ->toContain('Failed to communicate with the client:')
        ->toContain('401 Unauthorized');
});

it('fails with an invalid API key', function (): void {
    new Client('invalid-key', 'https://api.deepseek.com', 'deepseek-model');
    $command = new PeekCommand();
    $commandTester = new CommandTester($command);

    $filePath = 'tests/Fixtures/FilesToAnalyse/ClassWithErrors.php';

    $commandTester->execute(['file' => $filePath]);
    $output = $commandTester->getDisplay();

    expect($output)
        ->toContain('Failed to communicate with the client:')
        ->toContain('401 Unauthorized');
});

it('succeeds with a valid API key', function (): void {
    $clients = $this->config['clients'] ?? [];

    // Find a valid client
    $validClient = null;
    foreach ($clients as $clientName => $clientData) {
        if (!empty($clientData['api_key']) && !empty($clientData['url']) && !empty($clientData['model'])) {
            $validClient = $clientData;
            break;
        }
    }

    if (!$validClient) {
        $this->markTestSkipped('Valid API key not found in peek.json. Skipping test.');
    }

    $client = new Client($validClient['api_key'], $validClient['url'], $validClient['model']);
    $command = new PeekCommand($client);
    $commandTester = new CommandTester($command);

    $filePath = 'tests/Fixtures/FilesToAnalyse/SmallClassWithErrors.php';

    $commandTester->execute(['file' => $filePath]);
    $output = $commandTester->getDisplay();

    expect($output)
        ->toContain('Analyzing the entire file:')
        ->toContain('Analysis Result:')
        ->not->toContain('Incorrect API key provided.');
})->skip('Valid API key not found in peek.json. Skipping test.');
