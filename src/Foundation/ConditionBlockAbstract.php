<?php declare(strict_types=1);

namespace Tetraquark\Foundation;
use \Tetraquark\{Exception, Block, Log, Validate, Str};

abstract class ConditionBlockAbstract extends BlockAbstract
{
    protected array $endChars = [
        '}' => true
    ];

    protected string $condition = '';
    /** @var array Here we save blocks for later aliasing */
    protected array $condBlocks = [];
    protected string $condType = '';
    protected string $singleCond = '';

    abstract protected function getArgs(): string;

    protected const SINGLE_CONDITION_SUBTYPE = 'single-condition';

    protected function setConditionAndInstruction(int $start)
    {
        $actualStart = $start - \mb_strlen($this->getCondType());
        $condStart = null;
        $condEnd   = null;
        $end       = null;
        $letterFound = false;
        $parenthesisOpen = 0;
        for ($i=$actualStart; $i < self::$content->getLength(); $i++) {
            $letter = self::$content->getLetter($i);
            if ($letter == ' ') {
                continue;
            }

            if (
                ($startsTemplate = Validate::isTemplateLiteralLandmark($letter, ''))
                || Validate::isStringLandmark($letter, '')
            ) {
                $i = $this->skipString($letter, $i + 1, self::$content, $startsTemplate);

                if (\is_null(self::$content->getLetter($i))) {
                    break;
                }

                $letter = self::$content->getLetter($i);
            }

            if ($parenthesisOpen > 0 && $letter == ')') {
                $parenthesisOpen--;
                continue;
            }

            if (!\is_null($condStart) && $letter == '(') {
                $parenthesisOpen++;
                continue;
            }

            if ($parenthesisOpen > 0) {
                continue;
            }

            if (\is_null($condStart) && $letter !== '(') {
                continue;
            } elseif (\is_null($condStart) && $letter == '(') {
                $condStart = $i + 1;
                continue;
            }

            if (\is_null($condEnd) && !\is_null($condStart) && $letter == ')') {
                $condEnd = $i;
                continue;
            }

            if (!\is_null($condEnd) && $letter != "\n" && $letter != "{") {
                $letterFound = true;
            }

            if (
                !\is_null($condStart)
                && !\is_null($condEnd)
                && (
                    (
                        $letter == '{'
                        && !$letterFound
                    ) || (
                        (
                            $this instanceof Block\IfBlock
                            || $this instanceof Block\WhileBlock
                            || $this instanceof Block\ForBlock
                        )
                        && ($letter == ';' || $letter == "\n")
                        && $letterFound
                    )
                )
            ) {
                $end = $i + 1;
                if ($letter == ';' || $letter == "\n") {
                    $this->setSubtype(self::SINGLE_CONDITION_SUBTYPE);
                    $end--;
                }
                break;
            }

            if (
                !\is_null($condStart)
                && !\is_null($condEnd)
                && !(
                    $this instanceof Block\IfBlock
                    || $this instanceof Block\WhileBlock
                    || $this instanceof Block\ForBlock
                )
            ) {
                throw new Exception("Unexcepted character when searching for condition block start at letter $i => " . $this::class, 401);
            }
        }

        if (is_null($end)) {
            $this->setSubtype(self::SINGLE_CONDITION_SUBTYPE);
            $end = self::$content->getLength();
        }
        if (\is_null($condStart) || \is_null($condEnd)) {
            throw new Exception("Condition (" . $this->getCondType() . " at letter $start) was not mapped properly, stopping script", 500);
        }
        $this->setCaret($end);
        $this->setInstruction(self::$content->iCutToContent($actualStart, $end - 1));
        $this->setInstructionStart($actualStart);
        $this->setCondition(self::$content->iSubStr($condStart, $condEnd - 1));
        if ($this->getSubtype() === self::SINGLE_CONDITION_SUBTYPE) {
            $this->setSingleCond(self::$content->iSubStr($condEnd + 1, $end - 1));
        }
    }

    protected function setCondition(string $condition): void
    {
        $this->condition = trim($condition);
    }

    protected function getCondition(): string
    {
        return $this->condition;
    }

    protected function setCondBlocks(array $blocks): void
    {
        foreach ($blocks as $i => &$block) {
            $block->setPlacement('getCondBlocks');
            $block->setChildIndex($i);
        }
        $this->condBlocks = $blocks;
    }

    public function getCondBlocks(): array
    {
        return $this->condBlocks;
    }

    public function recreate(): string
    {
        $script = $this->getCondType() . '(' . $this->getArgs() . ')';
        $blocks = $this->getBlocks();
        if ($this->getSubtype() !== self::SINGLE_CONDITION_SUBTYPE || sizeof($blocks) == 0) {
            $script .= '{';
        }

        foreach ($blocks as $block) {
            $script .= $block->recreate();
        }

        if ($this->getSubtype() !== self::SINGLE_CONDITION_SUBTYPE || sizeof($blocks) == 0) {
            $script .= '}';
        } elseif ($this->getSubtype() === self::SINGLE_CONDITION_SUBTYPE) {
            // $script = rtrim($script, ';') . ';';
        }

        return $script;
    }

    protected function recreateCondBlocks(array $blocks): string
    {
        $str = '';
        foreach ($blocks as $block) {
            $str .= rtrim($block->recreate(), ';');
        }
        return $str;
    }

    protected function setCondType(string $type): void
    {
        $this->condType = $type;
    }

    public function getCondType(): string
    {
        return $this->condType;
    }

    protected function setSingleCond(string $single): void
    {
        $this->singleCond = $single;
    }

    public function getSingleCond(): string
    {
        return $this->singleCond;
    }
}
