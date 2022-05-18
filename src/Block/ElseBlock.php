<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Content};
use \Tetraquark\Foundation\BlockAbstract as Block;

class ElseBlock extends Block implements Contract\Block
{
    protected const ELSEIF = 'elseif';
    protected const SINGLE_LINE = 'single line';
    protected array $endChars = [
        "}" => true
    ];

    public function objectify(int $start = 0)
    {
        $this->setName('');
        $this->setInstructionStart($start - \mb_strlen('else '));
        [$letter, $pos] = $this->getNextLetter($start, self::$content);
        if (
            $letter == 'i'
            && self::$content->getLetter($pos + 1) == 'f'
            && (
                Validate::isWhitespace(self::$content->getLetter($pos + 2))
                || self::$content->getLetter($pos + 2) == '('
            )
        ) {
            $this->setSubtype(self::ELSEIF);
            $this->setCaret($start);
            $this->setInstruction(new Content('else '));
        } else {
            $this->setInstruction(new Content('else{'));
            if (self::$content->subStr(0) == '{') {
                $this->setCaret($start);
                $this->blocks = array_merge($this->blocks, $this->createSubBlocks($start + 1));
            } else {
                [$letter, $pos] = $this->getNextLetter($start, self::$content);
                if ($letter != '{') {
                    $this->setInstruction(new Content('else'));
                    $this->setSubtype(self::SINGLE_LINE);
                    $this->endChars = [
                        "\n" => true,
                        ";" => true,
                    ];
                }
                $this->blocks = array_merge($this->blocks, $this->createSubBlocks($pos + 1));
            }
        }
    }

    public function recreate(): string
    {
        $script = 'else';
        $blocks = $this->getBlocks();
        if ($this->getSubtype() === self::ELSEIF) {
            return $script . ' ';
        } else {
            if ($this->getSubType() != self::SINGLE_LINE) {
                $script .= '{';
            } else {
                $script .= ' ';
            }
            foreach ($blocks as $block) {
                $script .= $block->recreate();
            }
            if ($this->getSubType() != self::SINGLE_LINE) {
                return $script . '}';
            }
            return rtrim($script, ';') . ';';
        }
    }
}
