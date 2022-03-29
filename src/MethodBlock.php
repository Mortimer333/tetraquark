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

    protected function findAndSetArguments(): void
    {
        $instr = $this->getInstruction();
        $startSettingArgs = false;
        $word = '';
        for ($i=\strlen($instr) - 1; $i >= 0; $i--) {
            $letter = $instr[$i];
            if (!$startSettingArgs && $letter == ')') {
                $startSettingArgs = true;
                continue;
            }

            if ($startSettingArgs && $this->isWhitespace($letter)) {
                continue;
            }

            if ($startSettingArgs && $letter == '(') {
                $this->addArgument(strrev($word));
                break;
            }

            if ($startSettingArgs && $letter == ',') {
                $this->addArgument(strrev($word));
                $word = '';
                continue;
            }

            if ($startSettingArgs) {
                $word .= $letter;
            }
        }

        $this->arguments = array_reverse($this->arguments);
    }
}
