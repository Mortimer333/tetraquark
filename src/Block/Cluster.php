<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\Log as Log;
use \Tetraquark\Contract as Contract;
use \Tetraquark\Block as Block;

class Cluster extends Block implements Contract\Block
{
    public function objectify(int $start = 0)
    {
        $this->setInstruction('[]')
            ->setInstructionStart($start)
        ;
        $this->setCaret($start + 2);
    }

    public function recreate(): string
    {
        return $this->getInstruction() . ';';
    }
}
