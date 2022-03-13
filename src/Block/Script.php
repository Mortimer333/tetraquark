<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\Contract as Contract;
use \Tetraquark\Block as Block;

class Script extends Block implements Contract\Block
{
    public function objectify()
    {
        echo 'Script';
    }
}
