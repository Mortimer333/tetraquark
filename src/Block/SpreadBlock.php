<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate};
use \Tetraquark\Foundation\BlockAbstract as Block;

class SpreadBlock extends Block implements Contract\Block
{
    public function objectify(int $start = 0)
    {
        $this->setInstruction(new Content('...'));
        $this->setName('');
        $this->setInstructionStart($start - 2);
        $this->setCaret($start);
    }

    public function recreate(): string
    {
        return '...';
    }
}
