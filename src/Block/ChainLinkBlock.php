<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log as Log, Exception as Exception, Contract as Contract, Validate as Validate};
use \Tetraquark\Foundation\BlockAbstract as Block;

class ChainLinkBlock extends Block implements Contract\Block
{
    protected const FIRST = 'first';
    protected const MIDDLE = 'middle';
    protected const MIDDLE_BRACKET = 'middle:bracket';
    protected const END_METHOD = 'end:method';
    protected const END_VARIABLE = 'end:variable';
    public function __construct(
        int $start,
        protected string $subtype = '',
        protected array  $data  = []
    ) {
        parent::__construct($start, $subtype, $data);
    }
    public function objectify(int $start = 0)
    {
        $this->setName('');
        $endChars = array_merge([';' => true], Validate::getSpecial());

        if ($this->getSubtype() == self::FIRST) {
            $this->findInstructionStart($start, $endChars);
            $this->setCaret($start);
            return;
        }

        $end = null;
        $startLetterSearch = false;
        $caret = null;
        Log::log('====');
        list($letter, $linkStart) = $this->getNextLetter($start, self::$content);
        for ($i=$linkStart; $i < \mb_strlen(self::$content); $i++) {
            $letter = self::$content[$i];
            Log::log('Letter:' . $letter);
            if (($endChars[$letter] ?? false || $startLetterSearch) && !Validate::isWhitespace($letter)) {
                $end = $i;
                if ($letter == '=' && self::$content[$i + 1] != '=') {
                    $this->setSubtype(self::END_VARIABLE);
                } elseif ($letter == '(') {
                    $this->setSubtype(self::END_METHOD);
                    $i += 2;
                } elseif ($letter == '.') {
                    $this->setSubtype(self::MIDDLE);
                } elseif ($letter == '[') {
                    $this->setSubtype(self::MIDDLE_BRACKET);
                }

                $caret = $i - 1;
                break;
            }

            if (Validate::isWhitespace($letter)) {
                $startLetterSearch = true;
            }
        }
        if (\is_null($caret)) {
            $this->setCaret($i);
            $end = $i;
        } else {
            $this->setCaret($caret);
        }

        $instruction = \mb_substr(self::$content, $start, $end - $start);
        $instrLen = \mb_strlen($instruction);
        $this->setInstructionStart($start - 1)
            ->setInstruction(trim($instruction));

        if ($this->getSubtype() == self::END_METHOD) {
            $this->endChars = [
                ')' => true
            ];
            $this->blocks = array_merge($this->blocks, $this->createSubBlocks());
        } elseif ($this->getSubtype() == self::END_VARIABLE) {
            list($equal, $equalPos) = $this->getNextLetter($caret, self::$content);
            $attribute = new AttributeBlock($equalPos);
            $attribute->setName('');
            $this->setName($this->getInstruction());
            $this->setBlocks([
                $attribute
            ]);
            $this->setCaret($attribute->getCaret());
        }
    }

    public function recreate(): string
    {
        $script = $this->replaceVariablesWithAliases($this->getInstruction());
        $subtype = $this->getSubtype();

        if ($subtype == self::END_METHOD) {
            $script .= "(";
        }

        if ($subtype == self::MIDDLE || $subtype == self::FIRST) {
            $script .= '.';
        }

        foreach ($this->getBlocks() as $block) {
            $script .= rtrim($block->recreate(), ';');
        }

        if ($subtype == self::END_METHOD) {
            $parent = $this->getParent();
            $index = $this->getChildIndex();
            $parentChildren = $parent->getBlocks();
            $nextChild = $parentChildren[$index + 1] ?? null;
            if (
                (
                    (
                        $nextChild instanceof ChainLinkBlock
                        || $nextChild instanceof BracketChainLinkBlock
                    )
                    && $nextChild->getSubtype() !== self::FIRST
                )
                || $this->checkIfFirstLetterInNextSiblingIsADot()
            ) {
                $script .= ")";
            } else {
                $script .= ");";
            }
        } elseif ($subtype !== self::MIDDLE && $subtype !== self::FIRST && $subtype !== self::MIDDLE_BRACKET) {
            $script .= ";";
        }

        return $script;
    }
}
