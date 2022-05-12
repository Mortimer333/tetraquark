<?php declare(strict_types=1);

namespace Tetraquark\Foundation;
use \Tetraquark\{Exception, Block, Log, Validate};
use \Tetraquark\Str;

abstract class MethodBlockAbstract extends BlockAbstract
{
    /** @var array Contains arguments in form of Blocks[] so its [Blocks[], Blocks[]] */
    protected array $arguments = [];
    protected string $status = '';
    protected const CREATING_ARGUMENTS = 'creating arguments';

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
            $args = trim($args) . ',';
        }
        return rtrim($args, ',');
    }

    protected function findAndSetArguments(): void
    {
        $instr = $this->getInstruction();
        $startSettingArgs = false;
        $word = '';
        $arguments = [];
        $skipBracket = 0;
        for ($i=$instr->getLength() - 1; $i >= 0; $i--) {
            $letter = $instr->getLetter($i);
            if (
                ($startsTemplate = Validate::isTemplateLiteralLandmark($letter, ''))
                || Validate::isStringLandmark($letter, '')
            ) {
                $oldPos = $i;
                $i = $this->skipString($i - 1, $instr, $startsTemplate, true);
                if (\is_null($instr->getLetter($i))) {
                    break;
                }
                $word .= Str::rev($instr->subStr($i + 1, $oldPos - $i));
                $letter = $instr->getLetter($i);
            }

            if ($startSettingArgs && $skipBracket > 0 && ($letter == '{' || $letter == '(' || $letter == '[')) {
                $skipBracket--;
                $word .= $letter;
                continue;
            }

            if ($startSettingArgs && ($letter == '}' || $letter == ')' || $letter == ']')) {
                $skipBracket++;
                $word .= $letter;
                continue;
            }

            if ($skipBracket > 0) {
                $word .= $letter;
                continue;
            }

            if (!$startSettingArgs && $letter == ')') {
                $startSettingArgs = true;
                continue;
            }

            if ($startSettingArgs && Validate::isWhitespace($letter)) {
                $word .= $letter;
                continue;
            }

            if ($startSettingArgs && $letter == '(') {
                $arguments[] = Str::rev($word);
                $word = '';
                break;
            }

            if ($startSettingArgs && $letter == ',') {
                $arguments[] = Str::rev($word);
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
        $this->setStatus(self::CREATING_ARGUMENTS);
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

    protected function setStatus(string $status): void
    {
        $this->status = $status;
    }

    protected function getStatus(): string
    {
        return $this->status;
    }

    protected function findMethodEnd(int $start)
    {
        $properEnd = null;
        $startDefaultSkip = false;
        for ($i=$start; $i < self::$content->getLength(); $i++) {
            $letter = self::$content->getLetter($i);

            if (
                ($startsTemplate = Validate::isTemplateLiteralLandmark($letter, ''))
                || Validate::isStringLandmark($letter, '')
            ) {
                $i = $this->skipString($i + 1, self::$content, $startsTemplate);
                $letter = self::$content->getLetter($i);
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
        $this->setInstructionStart($properStart)
            ->setInstruction(self::$content->iSubStr($properStart, $properEnd));
    }
}
