<?php declare(strict_types=1);

namespace Tetraquark;

class MethodBlock extends Block
{
    protected array $arguments = [];
    protected array $argumentsAliases = [];

    public function addArgumentAlias(string $arg, string $alias): self
    {
        $this->argumentsAliases[$arg] = $alias;
        return $this;
    }

    public function getArgumentsAliases(): array
    {
        return $this->argumentsAliases;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    protected function addArgument(string $argument): self
    {
        if (\mb_strlen($argument) == 0) {
            return $this;
        }
        $this->arguments[] = $argument;
        return $this;
    }

    protected function getAliasedArguments(): string
    {
        $args = '';
        $argAliases = $this->getArgumentsAliases();
        foreach ($this->getArguments() as $arg) {
            $args .= $argAliases[$arg] . ',';
        }
        return rtrim($args, ',');
    }
}
