<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log as Log, Exception as Exception, Contract as Contract, Validate as Validate};
use \Tetraquark\Abstract\VariableBlockAbstract;

class VariableBlock extends VariableBlockAbstract implements Contract\Block
{
    protected string $value = '';
    protected array $endChars = [
        ';' => true,
        "\n" => true
    ];

    protected array $instructionEnds = [
        '=' => true,
        ';' => true,
    ];

    public function objectify(int $start = 0)
    {
        $newLineFound = false;
        for ($i=$start + 1; $i < \mb_strlen(self::$content); $i++) {
            $letter = self::$content[$i];

            if (Validate::isWhitespace($letter)) {
                continue;
            }

            if ($letter == '=') {
                break;
            }

            if ($letter == ';' || $newLineFound) {
                $this->setInstruction('');
                $this->setInstructionStart($i);
                $this->setName(\mb_substr(self::$content, $start, $i - $start));
                $this->setCaret($i + 1);
                return;
            }

            if ($letter == "\n") {
                $newLineFound = true;
                continue;
            }
        }

        $this->findInstructionEnd($start, $this->subtype, $this->instructionEnds);

        $this->blocks = array_merge($this->blocks, $this->createSubBlocks());
        if (\sizeof($this->blocks) == 0) {
            $instrEnd = $this->getInstructionStart() + \mb_strlen($this->getInstruction());
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
