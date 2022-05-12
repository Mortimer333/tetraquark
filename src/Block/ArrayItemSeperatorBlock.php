<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Content};
use \Tetraquark\Foundation\BlockAbstract as Block;

class ArrayItemSeperatorBlock extends Block implements Contract\Block
{
    protected array $endChars = [
        '[' => true,
        ',' => true,
    ];

    public function objectify(int $start = 0)
    {
        $this->setInstruction(new Content(''));
        $this->setName('');
        $this->setCaret($start);
        $this->setInstructionStart($start);
    }

    public function recreate(): string
    {
        return ',';
    }
}
