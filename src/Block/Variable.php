<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Xeno\X as Xeno;
use \Tetraquark\Log as Log;
use \Tetraquark\Contract as Contract;
use \Tetraquark\Block as Block;

class Variable extends Block implements Contract\Block
{
    protected string $value = '';
    protected array $endChars = [
        ';' => true,
        "\n" => true
    ];

    protected array $instructionEnds = [
        '=' => true,
    ];

    public function objectify(int $start = 0)
    {
        $this->findInstructionEnd($start, $this->subtype, $this->instructionEnds);
        $this->createSubBlocks();
        if (\sizeof($this->blocks) == 0) {
            $instrEnd = $this->getInstructionStart() + $this->getInstructionLength();
            $this->setValue(trim(substr(self::$content, $instrEnd, $this->getCaret() - $instrEnd)));
        }
        $this->findAndSetName($this->getSubtype() . ' ', ['=' => true]);
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

    protected function isStartingString(string $letter): bool
    {
        $stringStarts = [
            '"' => '"',
            "'" => "'",
            '`' => '`',
        ];
        return $stringStarts[$letter] ?? false;
    }

    public function recreate(): string
    {
        $script = $this->getSubType() . ' ' . $this->getAlias() . '=';

        foreach ($this->getBlocks() as $block) {
            $script .= $block->recreate();
        }

        $value = $this->getValue();
        if (\mb_strlen($value) > 0) {
            $script .= $this->replaceVariablesWithAliases($value) . ';';
        }
        return $script;
    }
}
