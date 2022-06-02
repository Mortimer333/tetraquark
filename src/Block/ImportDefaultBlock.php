<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Content};
use \Tetraquark\Foundation\BlockAbstract as Block;

class ImportDefaultBlock extends Block implements Contract\Block
{
    public function objectify(int $start = 0)
    {
        $this->setInstructionStart($start - \mb_strlen('default '))
            ->setCaret($start)
            ->setInstruction(new Content('default'))
        ;
    }

    public function recreate(): string
    {
        return " default ";
    }
}
