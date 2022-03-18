<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Xeno\X as Xeno;
use \Tetraquark\Log as Log;
use \Tetraquark\Contract as Contract;
use \Tetraquark\Block as Block;

class Method extends Block implements Contract\Block
{
    public function objectify(int $start = 0)
    {
        $this->findInstructionEnd($start, 'function', '{');

        if ($this->isMultiLine()) {
            $this->endFunction = true;
        }
    }

    public function isMultiLine(): bool
    {
        return true;
    }
}
