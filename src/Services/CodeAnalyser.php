<?php

namespace Peek\Services;

use Peek\Contracts\ClientInterface;
use Peek\Contracts\CodeAnalyserContract;
use Peek\ValueObject\CodeSnippet;

class CodeAnalyser implements CodeAnalyserContract
{
    public function __construct(private readonly ClientInterface $client) {}

    public function analyse(CodeSnippet $codeSnippet): string
    {
        return $this->client->ask($codeSnippet->getCode());
    }
}
