<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Content};
use \Tetraquark\Foundation\BlockAbstract as Block;
use \Tetraquark\Contract\{Block as BlockInterface};

class InstanceofBlock extends Block implements Contract\Block
{
    public function objectify(int $start = 0)
    {
        $this->setName('');
        $this->setInstructionStart($start - 11);
        list($word, $pos) = $this->getNextWord($start, self::$content);
        $this->setCaret($pos)
            ->setInstruction(new Content(' instanceof ' . $word))
        ;
    }

    public function recreate(): string
    {
        return $this->getInstruction()->__toString();
    }
}
