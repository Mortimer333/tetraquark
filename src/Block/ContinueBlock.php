<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Content};
use \Tetraquark\Foundation\BlockAbstract as Block;
use \Tetraquark\Contract\{Block as BlockInterface};

class ContinueBlock extends Block implements Contract\Block
{
    protected string $continueLabel = '';
    public function objectify(int $start = 0)
    {
        $this->setName('');
        $this->setInstructionStart($start - 9);
        list($letter, $pos) = $this->getNextLetter($start, self::$content);
        if ($letter === ';') {
            $this->setCaret($pos)
                ->setInstruction(new Content('continue;'));
            return;
        }

        list($word, $pos) = $this->getNextWord($start, self::$content);
        $this->setContinueLabel($word)
            ->setCaret($pos)
            ->setInstruction(new Content('continue ' . $this->getContinueLabel() . ';'));
    }

    public function recreate(): string
    {
        return $this->getInstruction()->__toString();
    }

    protected function setContinueLabel(string $continueLabel): self
    {
        $this->continueLabel = rtrim($continueLabel, ';');
        return $this;
    }

    public function getContinueLabel(): string
    {
        return $this->continueLabel;
    }
}
