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
    protected Block $methodValues;

    public function objectify(int $start = 0)
    {
        $this->setName('');
        $endChars = array_merge([';' => true], Validate::getSpecial());

        if ($this->getSubtype() == self::FIRST) {
            $this->findInstructionStart($start, $endChars);
            $this->blocks = array_merge($this->blocks, $this->createSubBlocks($start, true));
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
            $this->methodValues = new CallerBlock($this->getCaret() - 1, '', $this);$this->createSubBlocks();
            $this->methodValues->setChildIndex(0);
            $this->setCaret($this->methodValues->getCaret() + 1);
            // $this->blocks = array_merge($this->blocks, $this->createSubBlocks(onlyOne: true));
        } elseif ($this->getSubtype() == self::END_VARIABLE) {
            // list($equal, $equalPos) = $this->getNextLetter($caret, self::$content);
            // $attribute = new AttributeBlock($equalPos, '', $this);
            // $attribute->setName('');
            // $this->setName($this->getInstruction()->subStr(0));
            // $this->setBlocks([
            //     $attribute
            // ]);
            // $this->setCaret($attribute->getCaret());
        }

        $this->blocks = array_merge($this->blocks, $this->createSubBlocks(onlyOne: true));
        if ($this->getSubtype() !== self::END_METHOD && $this->getSubtype() !== self::END_VARIABLE) {
        }
    }

    public function recreate(): string
    {
        $script = $this->replaceVariablesWithAliases($this->getInstruction());
        $subtype = $this->getSubtype();

        if ($subtype == self::END_METHOD) {
            // $script .= "(";

            $script .= $this->methodValues->recreate();

            // $script .= ")";
        }

        $blocks = $this->getBlocks();
        if (\sizeof($blocks)) {
            $block = $blocks[0];

            if ($block::class === $this::class) {
                $script .= '.';
            }

            $script .= rtrim($block->recreate(), ';');
        }


        return $script . ';';
    }
}
