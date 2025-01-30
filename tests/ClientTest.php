<?php

use Peek\Contracts\ClientInterface;
use Peek\Services\CodeAnalyser;
use Peek\ValueObject\CodeSnippet;

it('returns response', function (): void {
    $mockClient = $this->createMock(ClientInterface::class);
    $mockClient->method('ask')
        ->willReturn('Mocked analysis response');

    $code = "<?php echo 'Hello, World!';";
    $codeSnippet = new CodeSnippet($code, 1, 10);
    $codeAnalyser = new CodeAnalyser($mockClient);

    $response = $codeAnalyser->analyse($codeSnippet);
    expect($response)->toBe('Mocked analysis response');
});
