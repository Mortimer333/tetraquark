<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log as Log, Exception as Exception, Contract as Contract, Validate as Validate};
use \Tetraquark\Abstract\BlockAbstract as Block;

class SpreadBlock extends Block implements Contract\Block
{
    public function objectify(int $start = 0)
    {
        $this->setInstruction('...');
        $this->setName('');
        $this->setInstructionStart($start);
        $this->setCaret($start);
    }

    public function recreate(): string
    {
        return '...';
    }
}
