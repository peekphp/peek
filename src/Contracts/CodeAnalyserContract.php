<?php

namespace Peek\Contracts;

use Peek\ValueObject\CodeSnippet;

interface CodeAnalyserContract
{
    public function analyse(CodeSnippet $codeSnippet): string;
}
