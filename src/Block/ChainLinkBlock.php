<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate};
use \Tetraquark\Foundation\BlockAbstract as Block;

class ChainLinkBlock extends Block implements Contract\Block
{
    public const FIRST = 'first';
    public const MIDDLE = 'middle';
    public const MIDDLE_BRACKET = 'middle:bracket';
    public const END_METHOD = 'end:method';
    public const END_VARIABLE = 'end:variable';
    protected Contract\Block $methodValues;

    public function objectify(int $start = 0)
    {
        $this->setName('');
        $endChars = array_merge([';' => true], Validate::getSpecial());
        $this->endChars = array_merge($this->endChars, [']' => true]);
        if ($this->getSubtype() == self::FIRST) {
            $this->findInstructionStart($start, $endChars);
            $this->blocks = array_merge($this->blocks, $this->createSubBlocks($start, true));
            // If chain didn't and with new line then it must have ended on someone elses end symbol
            $endChar = self::$content->getLetter($this->getCaret());
            if ($endChar === ']' && !($this->getLastLink() instanceof BracketChainLinkBlock)) {
                $this->setCaret($this->getCaret() - 1);
            }
            return;
        }

        $end = null;
        $startLetterSearch = false;
        $caret = null;
        list($letter, $linkStart) = $this->getNextLetter($start, self::$content);
        for ($i=$linkStart; $i < self::$content->getLength(); $i++) {
            $letter = self::$content->getLetter($i);
            if (($endChars[$letter] ?? false || $startLetterSearch) && $letter != ' ') {
                $end = $i;
                if ($letter == '=' && self::$content->getLetter($i + 1) != '=') {
                    $this->setSubtype(self::END_VARIABLE);
                } elseif ($letter == '(') {
                    $this->setSubtype(self::END_METHOD);
                    $i += 1;
                } elseif ($letter == '.') {
                    $this->setSubtype(self::MIDDLE);
                } elseif ($letter == '[') {
                    $this->setSubtype(self::MIDDLE_BRACKET);
                }

                $caret = $i;
                $this->setCaret($caret);
                break;
            }

            if (Validate::isWhitespace($letter)) {
                $startLetterSearch = true;
            }
        }
        if (\is_null($caret)) {
            $this->setCaret($i);
            $end = $i;
        }

        $this->setInstructionStart($start - 1)
            ->setInstruction(self::$content->iCutToContent($start, $end)->trim());

        if ($this->getSubtype() == self::END_METHOD) {
            $this->methodValues = new CallerBlock($this->getCaret() - 1, '', $this);
            $this->methodValues->setChildIndex(0);
            $this->setCaret($this->methodValues->getCaret() + 1);
            $this->blocks = array_merge($this->blocks, $this->createSubBlocks(onlyOne: true));
        } elseif ($this->getSubtype() == self::END_VARIABLE) {
            $this->methodValues = new AttributeBlock($this->getCaret(), '', $this);
            $this->methodValues->setChildIndex(0);
            $this->methodValues->setName('');
            $this->setCaret($this->methodValues->getCaret() + 1);
            $this->setName($this->getInstruction()->__toString());
        } else {
            list($nextLetter, $pos) = $this->getNextLetter($this->getCaret(), self::$content);
            $possibleOperation = $nextLetter . self::$content->getLetter($pos + 1);
            if ($possibleOperation === '--' || $possibleOperation == '++') {
                $symbol = new SymbolBlock($pos + 1, $possibleOperation, $this);
                $symbol->setChildIndex(0);
                $this->setBlocks([$symbol]);
                $this->setCaret($pos + 1);
            } else {
                $this->blocks = array_merge($this->blocks, $this->createSubBlocks(onlyOne: true));
            }
        }

    }

    public function recreate(): string
    {
        $script = $this->replaceVariablesWithAliases($this->getInstruction());
        $subtype = $this->getSubtype();

        if ($subtype == self::END_METHOD || $subtype == self::END_VARIABLE) {
            $script .= rtrim($this->methodValues->recreate(), ';');
        }

        $blocks = $this->getBlocks();
        if (\sizeof($blocks)) {
            $block = $blocks[0];

            if ($block::class === $this::class) {
                $script .= '.';
            }

            $script .= rtrim($block->recreate(), ';');
        }

        if (!$this->isNextSiblingContected()) {
            return $script . ';';
        }
        return $script;
    }

    public function getMethodValues(): ?Contract\Block
    {
        return $this->methodValues ?? null;
    }

    private function getLastLink(?Contract\Block $block = null): Contract\Block
    {
        if (is_null($block)) {
            $block = $this;
        }
        $link = $block->getBlocks()[\sizeof($block->getBlocks()) - 1] ?? null;
        if (\is_null($link)) {
            return $block;
        }
        return $this->getLastLink($link);
    }
}
