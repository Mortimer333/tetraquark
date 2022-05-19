<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Content};
use \Tetraquark\Foundation\MethodBlockAbstract as MethodBlock;

class ArrowFunctionBlock extends MethodBlock implements Contract\Block
{
    protected const PARENTHESIS = 'parenthesis';
    protected const NO_PARENTHESIS = 'no-parenthesis';
    protected const BRACKETS = 'brackets';
    protected const NO_BRACKETS = 'no-brackets';
    protected string $value = '';
    /*
        Possible function syntaxes:
        - () => {}
        - x => {}
        - x => x + 1
        - (x) => x + 1
        We can check if its a function if there is `=>`
    */
    public function objectify(int $start = 0)
    {
        $subStart = $this->findStart($start);
        if (\is_null($subStart)) {
            throw new Exception('Start of arrow method not found', 404);
        }

        $subEnd = $this->findEnd($start);
        if (\is_null($subEnd)) {
            throw new Exception('End of arrow method not found', 404);
        }

        if ($this->isMultiLine()) {
            $instruction = str_replace("\n", ' ', self::$content->iSubStr($subStart, $subEnd));
        } else {
            $instruction = str_replace("\n", ' ', self::$content->iSubStr($subStart, $start + 1));
            $this->setValue(trim(str_replace("\n", ' ', self::$content->subStr($start + 1, $subEnd - $start))));
        }

        $this->setInstruction(new Content($instruction))
            ->setInstructionStart($subStart)
        ;
        if ($this->isMultiLine()) {
            $this->endFunction = true;
            $this->endChars = [
                '}' => true
            ];
            $this->blocks = array_merge($this->blocks, $this->createSubBlocks());
        }
        $this->findAndSetArguments();

        $this->setName('');
    }

    public function setValue(string $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    protected function findAndSetArguments(): void
    {
        if ($this->getSubtype() === self::PARENTHESIS . ':' . self::NO_BRACKETS || $this->getSubtype() === self::PARENTHESIS . ':' . self::BRACKETS) {
            parent::findAndSetArguments();
            return;
        }
        $instr = $this->getInstruction();
        $SFParenthesis = false;
        $SFWhitespace = false;
        $word = '';
        $arguments = [];
        for ($i=0; $i < $instr->getLength(); $i++) {
            $letter = $instr->getLetter($i);
            if (!$SFParenthesis && $letter == '(') {
                $SFParenthesis = true;
                $word = '';
                continue;
            }

            if (Validate::isWhitespace($letter)) {
                if ($SFWhitespace) {
                    if (\mb_strlen($word) > 0) {
                        $arguments[] = $word;
                    }
                    break;
                }
                continue;
            } elseif (!$SFParenthesis) {
                $word .= $letter;
                $SFWhitespace = true;
                continue;
            }

            if ($SFParenthesis && $letter == ',') {
                if (\mb_strlen($word) > 0) {
                    $arguments[] = $word;
                }
                $word = '';
                continue;
            }


            if ($SFParenthesis && $letter == ')') {
                if (\mb_strlen($word) > 0) {
                    $arguments[] = $word;
                }
                break;
            }

            $word .= $letter;
        }
        $this->setArgumentBlocks($arguments);
    }

    public function isMultiLine(): bool
    {
        return $this->getSubtype() == self::PARENTHESIS . ":" . self::BRACKETS || $this->getSubtype() == self::NO_PARENTHESIS . ':' . self::BRACKETS;
    }

    protected function findEnd(int $start):? int
    {
        $searchForEnd = false;
        $subEnd = null;
        for ($i=$start + 1; $i < self::$content->getLength(); $i++) {
            $letter = self::$content->getLetter($i);

            if ($searchForEnd && ($this->endChars[$letter] ?? false)) {
                $subEnd = $i - 1;
                $this->setCaret($i - 1);
                break;
            }

            if (!$searchForEnd && $letter == '{') {
                $this->setSubtype($this->getSubtype() . ':' . self::BRACKETS);
                $subEnd = $i + 1;
                $this->setCaret($i + 1);
                break;
            }

            if (!$searchForEnd && !Validate::isWhitespace($letter)) {
                $this->setSubtype($this->getSubtype() . ':' . self::NO_BRACKETS);
                $searchForEnd = true;
                continue;
            }
        }
        return $subEnd;
    }

    protected function findStart(int $start):? int
    {
        $searchForBracketsStart  = false;
        $searchForNextWhiteSpace = false;
        $subStart = null;
        $ignoreParenthesis = 0;
        for ($i=$start - 2; $i >= 0; $i--) {
            $letter = self::$content->getLetter($i);
            if (
                ($startsTemplate = Validate::isTemplateLiteralLandmark($letter, ''))
                || Validate::isStringLandmark($letter, '')
            ) {
                $i = $this->skipString($letter, $i - 1, self::$content, $startsTemplate, true);
                if (\is_null(self::$content->getLetter($i))) {
                    break;
                }
                $letter = self::$content->getLetter($i);
            }

            if ($letter == ';') {
                $subStart = $i + 1;
                break;
            }

            if ($searchForBracketsStart && $letter == ')') {
                $ignoreParenthesis++;
                continue;
            }

            if ($ignoreParenthesis > 0 && $letter == '(') {
                $ignoreParenthesis--;
                continue;
            }

            if ($ignoreParenthesis > 0) {
                continue;
            }

            if (!$searchForBracketsStart && $letter == ')') {
                $searchForBracketsStart = true;
                continue;
            }

            if ($searchForBracketsStart && $letter == '(') {
                $subStart = $i;
                $this->setSubtype(self::PARENTHESIS);
                break;
            }

            if (!$searchForBracketsStart && !$searchForNextWhiteSpace && !Validate::isWhitespace($letter)) {
                $searchForNextWhiteSpace = true;
                continue;
            }

            if ($searchForNextWhiteSpace && Validate::isWhitespace($letter)) {
                $subStart = $i + 1;
                $this->setSubtype(self::NO_PARENTHESIS);
                break;
            }
        }
        return $subStart;
    }

    public function recreate(): string
    {
        $args = $this->getArguments();
        $hasSpread = false;
        foreach ($args as $arg) {
            foreach ($arg as $block) {
                if ($block instanceof SpreadBlock) {
                    $hasSpread = true;
                }
            }
        }

        if (\sizeof($args) == 1 && !$hasSpread) {
            $script = $this->getAliasedArguments() . '=>';
        } else {
            $script = '(' . $this->getAliasedArguments() . ')=>';
        }
        if (!$this->isMultiLine()) {
            $script .= $this->replaceVariablesWithAliases(new Content($this->getValue())) . ';';
        } else {
            $script .= '{';
            $blocks = '';
            foreach ($this->getBlocks() as $block) {
                $blocks .= $block->recreate();
            }
            if (\mb_strlen($blocks) > 0) {
                $script .= rtrim($blocks, ';') . ';';
            }
            $script .= '};';
        }
        return $script;
    }
}
