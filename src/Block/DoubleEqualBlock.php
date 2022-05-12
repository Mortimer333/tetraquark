<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Content};
use \Tetraquark\Foundation\BlockAbstract as Block;

class DoubleEqualBlock extends Block implements Contract\Block
{
    public function objectify(int $start = 0)
    {
        $this->setInstruction(new Content('=='));
        $this->setName('');
        $this->setInstructionStart($start);
        $this->setCaret($start);
    }

    public function recreate(): string
    {
        return '==';
    }
}
