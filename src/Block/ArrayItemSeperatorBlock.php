<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log as Log, Exception as Exception, Contract as Contract, Validate as Validate};
use \Tetraquark\Abstract\BlockAbstract as Block;

class ArrayItemSeperatorBlock extends Block implements Contract\Block
{
    protected array $endChars = [
        '[' => true,
        ',' => true,
    ];

    public function objectify(int $start = 0)
    {
        $this->setInstruction('');
        $this->setName('');
        $this->setCaret($start);
        $this->setInstructionStart($start);
    }

    public function recreate(): string
    {
        return '';
    }
}
