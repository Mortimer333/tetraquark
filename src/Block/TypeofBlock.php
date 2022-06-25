<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Content};
use \Tetraquark\Foundation\BlockAbstract as Block;
use \Tetraquark\Contract\{Block as BlockInterface};

class TypeofBlock extends Block implements Contract\Block
{
    public function objectify(int $start = 0)
    {
        $this->setName('');
        $this->setInstructionStart($start - 7);
        $this->setCaret($start)
            ->setInstruction(new Content(' typeof '))
        ;
    }

    public function recreate(): string
    {
        return $this->getInstruction()->__toString();
    }
}
