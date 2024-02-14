<?php

declare(strict_types=1);

namespace ostark\PgConverter;

use ostark\PgConverter\StatementBuilder\BuilderResult\Result;

class MultilineStatement
{
    private array $lines = [];

    private bool $isCollecting = false;

    private string $stopCharacter = '';

    private ?\Closure $nextCallback;

    public function add(string $line): void
    {
        $this->isCollecting = true;
        $this->lines[] = rtrim($line);
    }

    public function setStopCharacter(string $char): void
    {
        $this->stopCharacter = $char;
    }

    public function isLastMultiline(string $line): bool
    {

        if ($line == '') {
            return false;
        }

        if ($this->isCollecting === false) {
            return false;
        }

        return str_starts_with(trim($line), $this->stopCharacter);
    }

    public function toString(): string
    {
        return implode(PHP_EOL, $this->lines);
    }

    public function isCollectingMultilines(): bool
    {
        return $this->isCollecting;
    }

    public function setNextHandler(\Closure $nextCallback): void
    {
        $this->nextCallback = $nextCallback;
    }

    public function next(): Result
    {

        if ($this->nextCallback === null) {
            throw new \RuntimeException('No next handler set');
        }

        $result = ($this->nextCallback)($this->toString());

        if ($result instanceof Result) {
            return $result;
        }

        throw new \RuntimeException(sprintf(
            'Callback did not return an instance of Result. Got "%s" instead.',
            gettype($result)
        ));

    }

    public function reset(): void
    {
        $this->lines = [];
        $this->isCollecting = false;
        $this->stopCharacter = '';
        $this->nextCallback = null;
    }
}
