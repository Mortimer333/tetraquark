<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Content};
use \Tetraquark\Foundation\BlockAbstract as Block;
use \Tetraquark\Contract\{Block as BlockInterface};

class BreakBlock extends Block implements Contract\Block
{
    protected string $breakLabel;
    public function objectify(int $start = 0)
    {
        $this->setName('');
        $this->setInstructionStart($start - 6);
        list($letter, $pos) = $this->getNextLetter($start, self::$content);
        if ($letter === ';') {
            $this->setCaret($pos)
                ->setInstruction(new Content('break;'));
            return;
        }

        list($word, $pos) = $this->getNextWord($start, self::$content);
        $this->setBreakLabel($word)
            ->setCaret($pos)
            ->setInstruction(new Content('break ' . $this->getBreakLabel()));
    }

    public function recreate(): string
    {
        return $this->getInstruction()->__toString();
    }

    protected function setBreakLabel(string $breakLabel): self
    {
        $this->breakLabel = rtrim($breakLabel, ';');
        return $this;
    }

    public function getBreakLabel(): string
    {
        return $this->breakLabel;
    }
}
