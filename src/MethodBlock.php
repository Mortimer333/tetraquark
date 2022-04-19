<?php declare(strict_types=1);

namespace Tetraquark;

class MethodBlock extends Block
{
    /** @var array Contains arguments in form of Blocks[] so its [Blocks[], Blocks[]] */
    protected array $arguments = [];

    public function getArguments(): array
    {
        return $this->arguments;
    }

    protected function addArgument(array $argument): self
    {
        if (\sizeof($argument) == 0) {
            return $this;
        }
        $this->arguments[] = $argument;
        return $this;
    }

    protected function getAliasedArguments(): string
    {
        $args = '';
        foreach ($this->getArguments() as $arg) {
            foreach ($arg as $block) {
                $args .= rtrim($block->recreate(), ';');
            }
            $args .= ',';
        }
        return rtrim($args, ',');
    }

    protected function findAndSetArguments(): void
    {
        $instr = $this->getInstruction();
        $startSettingArgs = false;
        $word = '';
        $arguments = [];
        for ($i=\strlen($instr) - 1; $i >= 0; $i--) {
            $letter = $instr[$i];
            if (!$startSettingArgs && $letter == ')') {
                $startSettingArgs = true;
                continue;
            }

            if ($startSettingArgs && $this->isWhitespace($letter)) {
                $word .= $letter;
                continue;
            }

            if ($startSettingArgs && $letter == '(') {
                $arguments[] = strrev($word);
                break;
            }

            if ($startSettingArgs && $letter == ',') {
                $arguments[] = strrev($word);
                $word = '';
                continue;
            }

            if ($startSettingArgs) {
                $word .= $letter;
            }
        }

        $arguments = array_reverse($arguments);
        $this->setArgumentBlocks($arguments);
    }

    protected function setArgumentBlocks(array $arguments): void
    {
        foreach ($arguments as $argument) {
            $blocks = $this->createSubBlocksWithContent($argument);
            foreach ($blocks as &$block) {
                if ($block instanceof Block\UndefinedBlock) {
                    $block->setName($block->getInstruction());
                }
            }
            $this->addArgument($blocks);
        }
    }

    protected function findMethodEnd(int $start)
    {
        $properEnd = null;
        $startDefaultSkip = false;
        for ($i=$start; $i < strlen(self::$content); $i++) {
            $letter = self::$content[$i];

            if (
                ($startsTemplate = $this->isTemplateLiteralLandmark($letter, ''))
                || $this->isStringLandmark($letter, '')
            ) {
                $i = $this->skipString($i + 1, self::$content, $startsTemplate);
                $letter = self::$content[$i];
            }

            if (($startDefaultSkip && $letter == ',') || $letter == ')') {
                $startDefaultSkip = false;
                continue;
            } elseif ($startDefaultSkip) {
                continue;
            }

            if ($letter == '=') {
                $startDefaultSkip = true;
                continue;
            }

            if ($letter == '{') {
                $properEnd = $i + 1;
                $this->setCaret($properEnd);
                break;
            }
        }

        if (is_null($properEnd)) {
            throw new Exception('Proper End not found', 404);
        }

        $properStart = $start;
        $instruction = substr(self::$content, $properStart, $properEnd - $properStart);
        $this->setInstructionStart($properStart)
            ->setInstruction($instruction);
    }
}
