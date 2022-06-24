<?php declare(strict_types=1);

namespace Tetraquark\Foundation;
use \Tetraquark\{Exception, Block, Log, Validate};
use \Tetraquark\Str;

abstract class MethodBlockAbstract extends BlockAbstract
{
    /** @var array Contains arguments in form of Blocks[] so its [Blocks[], Blocks[]] */
    protected array $arguments = [];
    protected string $status = '';
    protected string $prefix = '';
    public const CREATING_ARGUMENTS = 'method:create:argument';
    public const ASYNC_PREFIX = 'prefix:async';
    public const GET_PREFIX = 'prefix:get';
    public const SET_PREFIX = 'prefix:set';

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
                $i = $this->skipString($letter, $i - 1, $instr, $startsTemplate, true);
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
                $block->setPlacement('getArguments');
                if ($block instanceof Block\UndefinedBlock) {
                    $block->setName($block->getInstruction()->__toString());
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
                $i = $this->skipString($letter, $i + 1, self::$content, $startsTemplate);
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
            ->setInstruction(self::$content->iCutToContent($properStart, $properEnd - 1));
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    protected function setPrefix(string $prefix): self
    {
        $this->prefix = $prefix;
        return $this;
    }

    public function recreatePrefix(): string
    {
        return match ($this->prefix) {
            self::ASYNC_PREFIX => 'async ',
            self::SET_PREFIX   => 'set ',
            self::GET_PREFIX   => 'get ',
            default            => '',
        };
    }

    protected function checkForPrefixes(): void
    {
        list($word, $pos) = $this->getPreviousWord($this->getInstructionStart() - 1, self::$content);
        $possibleAsync = substr($word, -5);
        $realPos = $pos + \mb_strlen($word) - 5;

        if ($possibleAsync === 'async') {
            $this->setPrefix(self::ASYNC_PREFIX)
                ->setInstructionStart($realPos);
            return;
        }

        $possibleGetOrSet = substr($word, -3);
        $realPos = $pos + \mb_strlen($word) - 3;

        if ($possibleGetOrSet === 'get') {
            $this->setPrefix(self::GET_PREFIX)
                ->setInstructionStart($realPos);
            return;
        }

        if ($possibleGetOrSet === 'set') {
            $this->setPrefix(self::SET_PREFIX)
                ->setInstructionStart($realPos);
            return;
        }
    }
}
