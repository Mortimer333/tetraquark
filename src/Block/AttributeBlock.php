<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\Log as Log;
use \Tetraquark\Contract as Contract;
use \Tetraquark\VariableBlock as VariableBlock;

class AttributeBlock extends VariableBlock implements Contract\Block
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
        $letterFound = false;
        for ($i=$start - 1; $i >= 0; $i--) {
            $letter = self::$content[$i];
            if ($letterFound && $this->isWhitespace($letter)) {
                $start = $i;
                break;
            }

            if (!$this->isWhitespace($letter)) {
                $letterFound = true;
            }

            if ($i == 0) {
                $start = 0;
            }
        }
        $this->findInstructionEnd($start, $this->subtype, $this->instructionEnds);
        $this->blocks = array_merge($this->blocks, $this->createSubBlocks());
        if (\sizeof($this->blocks) == 0) {
            $instrEnd = $this->getInstructionStart() + \mb_strlen($this->getInstruction()) + 1;
            $this->setValue(trim(substr(self::$content, $instrEnd, $this->getCaret() - $instrEnd)));
        }
        $this->findAndSetName($this->getSubtype() . ' ', $this->instructionEnds);
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
}
