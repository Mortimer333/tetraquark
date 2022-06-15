<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Content};
use \Tetraquark\Foundation\VariableBlockAbstract;

class VariableItemBlock extends VariableBlockAbstract implements Contract\Block
{
    protected string $value = '';
    protected array $endChars = [
        ';' => true,
    ];

    protected array $instructionEnds = [
        '=' => true,
        ';' => true,
    ];

    public function objectify(int $start = 0)
    {
        for ($i=$start + 1; $i < self::$content->getLength(); $i++) {
            $letter = self::$content->getLetter($i);

            if (Validate::isWhitespace($letter)) {
                continue;
            }

            if ($letter == '=') {
                break;
            }

            if ($letter == ';') {
                $this->setInstruction(new Content(''));
                $this->setInstructionStart($i);
                $this->setName(self::$content->iSubStr($start, $i - 1));
                $this->setCaret($i + 1);
                return;
            }
        }
        $this->findInstructionEnd($start, $this->getSubtype(), $this->instructionEnds);
        $this->setInstruction(new Content($this->getInstruction() . '='));
        if (self::$content->getLetter($this->getCaret()) == '=') {
            $this->setCaret($this->getCaret() + 1);
        }
        $this->blocks = array_merge($this->blocks, $this->createSubBlocks(null));
        if (\sizeof($this->blocks) == 0) {
            $instrEnd = $this->getInstructionStart() + \mb_strlen($this->getInstruction());
            $this->setValue(trim(self::$content->iSubStr($instrEnd, $this->getCaret() - 1)));
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

    public function recreate(): string
    {
        $script = $this->getAlias($this->getName());

        if (\sizeof($this->getBlocks()) > 0 || \mb_strlen($this->getValue()) > 0) {
            $script .= '=';
        }

        foreach ($this->getBlocks() as $block) {
            $script .= rtrim(trim($block->recreate()), ';');
        }

        $value = $this->getValue();
        if (\mb_strlen($value) > 0) {
            $script .= $this->replaceVariablesWithAliases($value);
        }
        return $script;
    }
}
