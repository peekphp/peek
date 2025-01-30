<?php

namespace Peek\Contracts;

interface ClientInterface
{
    public function ask(string $prompt): string;
}
