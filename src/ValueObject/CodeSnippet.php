<?php

namespace Peek\ValueObject;

readonly class CodeSnippet
{
    public function __construct(private string $code, private int $startLine, private int $endLine) {}

    public function getCode(): string
    {
        return $this->code;
    }

    public function getStartLine(): int
    {
        return $this->startLine;
    }

    public function getEndLine(): int
    {
        return $this->endLine;
    }
}
