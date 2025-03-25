<?php

use Mockery\MockInterface;
use Peek\Client;
use Peek\Commands\PeekCommand;
use Symfony\Component\Console\Tester\CommandTester;

beforeEach(function (): void {
    makeJsonConfig('test-key', 'https://api.deepseek.com', 'deepseek-model');
});

afterEach(function (): void {
    if (file_exists('peek.json')) {
        unlink('peek.json');
    }
    Mockery::close();
});

function makeJsonConfig(?string $key = null, ?string $url = null, ?string $model = null): void
{
    $config = [
        'clients' => [
            'deepseek' => [
                'api_key' => $key ?? 'valid-key',
                'url' => $url ?? 'https://api.deepseek.com',
                'model' => $model ?? 'deepseek-model',
            ],
        ],
    ];

    file_put_contents('peek.json', json_encode($config, JSON_PRETTY_PRINT));
}

it('should create a new peek.json file with a false key', function (): void {
    makeJsonConfig('1234567890');

    expect(file_exists('peek.json'))->toBeTrue()
        ->and(file_get_contents('peek.json'))->toBeJson()
        ->and(json_decode(file_get_contents('peek.json'), true))
        ->toBe([
            'clients' => [
                'deepseek' => [
                    'api_key' => '1234567890',
                    'url' => 'https://api.deepseek.com',
                    'model' => 'deepseek-model',
                ],
            ],
        ]);
});

it('analyses the entire file successfully', function (): void {
    /** @var MockInterface&Client */
    $mockClient = Mockery::mock(Client::class);
    $mockClient->shouldReceive('ask')
        ->once()
        ->with(Mockery::type('string'))
        ->andReturn('Mocked analysis result for the file.');

    $command = new PeekCommand($mockClient);
    $commandTester = new CommandTester($command);

    $filePath = __DIR__.'/../Fixtures/FilesToAnalyse/ClassWithErrors.php';

    // Create the fixture file if it doesn't exist
    if (! file_exists($filePath)) {
        $fixtureDir = dirname($filePath);
        if (! is_dir($fixtureDir)) {
            mkdir($fixtureDir, 0777, true);
        }
        file_put_contents($filePath, "<?php\n\nclass ClassWithErrors {\n    public function brokenMethod() {\n        return 'This is a test';\n    }\n}");
    }

    $commandTester->execute(['file' => $filePath]);

    $output = $commandTester->getDisplay();

    expect($output)
        ->toContain('Analyzing the entire file:')
        ->toContain('Mocked analysis result for the file.');
});

it('analyses a snippet of code successfully', function (): void {
    /** @var MockInterface&Client */
    $mockClient = Mockery::mock(Client::class);
    $mockClient->shouldReceive('ask')
        ->once()
        ->with(Mockery::type('string'))
        ->andReturn('Mocked analysis result for the snippet.');

    $command = new PeekCommand($mockClient);
    $commandTester = new CommandTester($command);

    $filePath = __DIR__.'/../Fixtures/FilesToAnalyse/ClassWithErrors.php';

    // Create the fixture file if it doesn't exist
    if (! file_exists($filePath)) {
        $fixtureDir = dirname($filePath);
        if (! is_dir($fixtureDir)) {
            mkdir($fixtureDir, 0777, true);
        }
        file_put_contents($filePath, "<?php\n\nclass ClassWithErrors {\n    public function brokenMethod() {\n        return 'This is a test';\n    }\n}");
    }

    $lineRange = '3:5';

    $commandTester->execute(['file' => $filePath, '--lines' => $lineRange]);

    $output = $commandTester->getDisplay();

    expect($output)
        ->toContain("Analyzing snippet from lines $lineRange:")
        ->toContain('Mocked analysis result for the snippet.');
});

it('returns an error for a non-existent file', function (): void {
    /** @var MockInterface&Client */
    $mockClient = Mockery::mock(Client::class);

    $command = new PeekCommand($mockClient);
    $commandTester = new CommandTester($command);

    $filePath = 'nonexistent/path/ClassWithErrors.php';

    $commandTester->execute(['file' => $filePath]);

    $output = $commandTester->getDisplay();

    expect($output)
        ->toContain('The file at path')
        ->toContain('does not exist')
        ->toContain($filePath);
});

it('returns an error for invalid line range format', function (): void {
    /** @var MockInterface&Client */
    $mockClient = Mockery::mock(Client::class);

    $command = new PeekCommand($mockClient);
    $commandTester = new CommandTester($command);

    $filePath = __DIR__.'/../Fixtures/FilesToAnalyse/ClassWithErrors.php';

    // Create the fixture file if it doesn't exist
    if (! file_exists($filePath)) {
        $fixtureDir = dirname($filePath);
        if (! is_dir($fixtureDir)) {
            mkdir($fixtureDir, 0777, true);
        }
        file_put_contents($filePath, "<?php\n\nclass ClassWithErrors {\n    public function brokenMethod() {\n        return 'This is a test';\n    }\n}");
    }

    $invalidRange = 'invalid-format';

    $commandTester->execute(['file' => $filePath, '--lines' => $invalidRange]);

    $output = $commandTester->getDisplay();

    expect($output)
        ->toContain('Invalid lines format. Use "start:end" (e.g., 5:10).');
});

it('returns an error for an invalid line range that exceeds file lines', function (): void {
    /** @var MockInterface&Client */
    $mockClient = Mockery::mock(Client::class);

    $command = new PeekCommand($mockClient);
    $commandTester = new CommandTester($command);

    $filePath = __DIR__.'/../Fixtures/FilesToAnalyse/ClassWithErrors.php';

    // Create the fixture file if it doesn't exist
    if (! file_exists($filePath)) {
        $fixtureDir = dirname($filePath);
        if (! is_dir($fixtureDir)) {
            mkdir($fixtureDir, 0777, true);
        }
        file_put_contents($filePath, "<?php\n\nclass ClassWithErrors {\n    public function brokenMethod() {\n        return 'This is a test';\n    }\n}");
    }

    $invalidRange = '100:200';

    $commandTester->execute(['file' => $filePath, '--lines' => $invalidRange]);

    $output = $commandTester->getDisplay();

    expect($output)
        ->toContain('Invalid line range: 100 to 200.');
});

it('returns an error for a line range where start is greater than end', function (): void {
    /** @var MockInterface&Client */
    $mockClient = Mockery::mock(Client::class);

    $command = new PeekCommand($mockClient);
    $commandTester = new CommandTester($command);

    $filePath = __DIR__.'/../Fixtures/FilesToAnalyse/ClassWithErrors.php';

    // Create the fixture file if it doesn't exist
    if (! file_exists($filePath)) {
        $fixtureDir = dirname($filePath);
        if (! is_dir($fixtureDir)) {
            mkdir($fixtureDir, 0777, true);
        }
        file_put_contents($filePath, "<?php\n\nclass ClassWithErrors {\n    public function brokenMethod() {\n        return 'This is a test';\n    }\n}");
    }

    $invalidRange = '10:5';

    $commandTester->execute(['file' => $filePath, '--lines' => $invalidRange]);

    $output = $commandTester->getDisplay();

    expect($output)
        ->toContain('Invalid line range: 10 to 5.');
});

it('fails when no API key is present', function (): void {
    /** @var MockInterface&Client */
    $mockClient = Mockery::mock(Client::class);
    $mockClient->shouldReceive('ask')
        ->once()
        ->andThrow(new \RuntimeException('401 Unauthorized'));

    $command = new PeekCommand($mockClient);
    $commandTester = new CommandTester($command);

    $filePath = __DIR__.'/../Fixtures/FilesToAnalyse/ClassWithErrors.php';

    // Create the fixture file if it doesn't exist
    if (! file_exists($filePath)) {
        $fixtureDir = dirname($filePath);
        if (! is_dir($fixtureDir)) {
            mkdir($fixtureDir, 0777, true);
        }
        file_put_contents($filePath, "<?php\n\nclass ClassWithErrors {\n    public function brokenMethod() {\n        return 'This is a test';\n    }\n}");
    }

    $commandTester->execute(['file' => $filePath]);
    $output = $commandTester->getDisplay();

    expect($output)
        ->toContain('401 Unauthorized');
});

it('fails with an invalid API key', function (): void {
    /** @var MockInterface&Client */
    $mockClient = Mockery::mock(Client::class);
    $mockClient->shouldReceive('ask')
        ->once()
        ->andThrow(new \RuntimeException('Authentication Fails (no such user)'));

    $command = new PeekCommand($mockClient);
    $commandTester = new CommandTester($command);

    $filePath = __DIR__.'/../Fixtures/FilesToAnalyse/ClassWithErrors.php';

    // Create the fixture file if it doesn't exist
    if (! file_exists($filePath)) {
        $fixtureDir = dirname($filePath);
        if (! is_dir($fixtureDir)) {
            mkdir($fixtureDir, 0777, true);
        }
        file_put_contents($filePath, "<?php\n\nclass ClassWithErrors {\n    public function brokenMethod() {\n        return 'This is a test';\n    }\n}");
    }

    $commandTester->execute(['file' => $filePath]);
    $output = $commandTester->getDisplay();

    expect($output)
        ->toContain('Authentication Fails (no such user)');
});

it('succeeds with a valid API key', function (): void {
    /** @var MockInterface&Client */
    $mockClient = Mockery::mock(Client::class);
    $mockClient->shouldReceive('ask')
        ->once()
        ->andReturn('Successful analysis result');

    $command = new PeekCommand($mockClient);
    $commandTester = new CommandTester($command);

    $filePath = __DIR__.'/../Fixtures/FilesToAnalyse/ClassWithErrors.php';

    // Create the fixture file if it doesn't exist
    if (! file_exists($filePath)) {
        $fixtureDir = dirname($filePath);
        if (! is_dir($fixtureDir)) {
            mkdir($fixtureDir, 0777, true);
        }
        file_put_contents($filePath, "<?php\n\nclass ClassWithErrors {\n    public function brokenMethod() {\n        return 'This is a test';\n    }\n}");
    }

    $commandTester->execute(['file' => $filePath]);
    $output = $commandTester->getDisplay();

    expect($output)
        ->toContain('Analyzing the entire file:')
        ->toContain('Successful analysis result');
});
