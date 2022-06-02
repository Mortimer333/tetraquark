<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Content};
use \Tetraquark\Foundation\BlockAbstract as Block;

class ImportItemSeperatorBlock extends Block implements Contract\Block
{
    public function objectify(int $start = 0)
    {
        $this->setInstruction(new Content(''))
            ->setName('')
            ->setCaret($start)
            ->setInstructionStart($start)
        ;
    }

    public function recreate(): string
    {
        return ',';
    }
}
