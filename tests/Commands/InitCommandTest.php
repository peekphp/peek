<?php

use Peek\Commands\InitCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

afterEach(function (): void {
    if (file_exists('peek.json')) {
        unlink('peek.json');
    }
});

it('will pass and create a peek.json file when no peek.json exists', function (): void {
    $application = new Application;
    $application->add(new InitCommand);

    $command = $application->find('init');
    $commandTester = new CommandTester($command);

    // Simulate user input for client name, URL, and API key
    $commandTester->setInputs([
        'DeepSeek',                    // Select DeepSeek as the AI client
        'https://api.deepseek.com',    // API URL
        'test-api-key',                // API Key
        'default'                      // Model
    ]);

    $commandTester->execute([]);

    $output = $commandTester->getDisplay();

    expect(trim($output))->toContain('API key for deepseek saved successfully.');

    $config = json_decode(file_get_contents('peek.json'), true);

    expect($config['clients']['deepseek']['api_key'])->toBe('test-api-key')
        ->and($config['clients']['deepseek']['url'])->toBe('https://api.deepseek.com');
});

it('will fail when trying to reinitialize an existing client', function (): void {
    // Pre-create peek.json with an existing DeepSeek entry
    file_put_contents('peek.json', json_encode([
        'clients' => [
            'deepseek' => [
                'api_key' => 'existing-key',
                'url' => 'https://api.deepseek.com',
                'model' => 'default'
            ]
        ]
    ], JSON_PRETTY_PRINT));

    $application = new Application;
    $application->add(new InitCommand);

    $command = $application->find('init');
    $commandTester = new CommandTester($command);

    $commandTester->setInputs([
        'DeepSeek',                    // Try selecting DeepSeek again
    ]);

    $commandTester->execute([]);

    $output = $commandTester->getDisplay();

    expect(trim($output))->toContain('deepseek client already exists in the configuration.');
});

it('will allow adding a new AI client alongside existing ones', function (): void {
    // Pre-create peek.json with DeepSeek already configured
    file_put_contents('peek.json', json_encode([
        'clients' => [
            'deepseek' => [
                'api_key' => 'existing-key',
                'url' => 'https://api.deepseek.com',
                'model' => 'default'
            ]
        ]
    ], JSON_PRETTY_PRINT));

    $application = new Application;
    $application->add(new InitCommand);

    $command = $application->find('init');
    $commandTester = new CommandTester($command);

    $commandTester->setInputs([
        'OpenAI',                     // Select OpenAI as the new client
        'https://api.openai.com',     // API URL
        'new-api-key',                // API Key
        'default'                     // Model
    ]);

    $commandTester->execute([]);

    $output = $commandTester->getDisplay();

    expect(trim($output))->toContain('API key for openai saved successfully.');

    $config = json_decode(file_get_contents('peek.json'), true);

    expect($config['clients']['deepseek']['api_key'])->toBe('existing-key')
        ->and($config['clients']['deepseek']['url'])->toBe('https://api.deepseek.com')
        ->and($config['clients']['openai']['api_key'])->toBe('new-api-key')
        ->and($config['clients']['openai']['url'])->toBe('https://api.openai.com');
});
