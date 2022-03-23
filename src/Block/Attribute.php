<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\Log as Log;
use \Tetraquark\Contract as Contract;
use \Tetraquark\Block as Block;

class Attribute extends Block implements Contract\Block
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
        Log::increaseIndent();
        for ($i=$start - 1; $i >= 0; $i--) {
            $letter = self::$content[$i];
            Log::log('Letter: ' . $letter, 3);
            if ($letterFound && $this->isWhitespace($letter)) {
                Log::log('Start found: ' . $start, 3);
                $start = $i;
                break;
            }

            if (!$this->isWhitespace($letter)) {
                Log::log('Found closest letter, search for white space: ', 3);
                $letterFound = true;
            }
        }
        Log::decreaseIndent();
        $this->findInstructionEnd($start, $this->subtype, $this->instructionEnds);
        Log::log('Found instruction: ' . $this->getInstruction(), 3);
        $this->createSubBlocks();
        if (\sizeof($this->blocks) == 0) {
            $instrEnd = $this->getInstructionStart() + $this->getInstructionLength();
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
