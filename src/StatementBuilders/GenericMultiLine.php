<?php

namespace ostark\PgConverter\StatementBuilders;

class GenericMultiLine
{
    private array $lines = [];

    private bool $isCollecting = false;

    private string $stopCharacter = '';

    public function __construct(public ?string $name = null)
    {
        // ...
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function reset(): void
    {
        $this->lines = [];
        $this->isCollecting = false;
        $this->stopCharacter = '';
        $this->name = null;
    }
}
