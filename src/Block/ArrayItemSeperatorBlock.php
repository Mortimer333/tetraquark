<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\Log as Log;
use \Tetraquark\Contract as Contract;
use \Tetraquark\Block as Block;

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
