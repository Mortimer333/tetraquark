<?php declare(strict_types=1);

namespace Tetraquark;

class MethodBlock extends Block
{
    protected array $arguments = [];

    public function getArguments(): array
    {
        return $this->arguments;
    }

    protected function addArgument(string $argument): self
    {
        $argument = trim($argument);
        if (\mb_strlen($argument) == 0) {
            return $this;
        }
        $this->arguments[] = $argument;
        return $this;
    }

    protected function getAliasedArguments(): string
    {
        $args = '';
        foreach ($this->getArguments() as $arg) {
            $args .= $this->getAlias($arg) . ',';
        }
        return rtrim($args, ',');
    }
}
