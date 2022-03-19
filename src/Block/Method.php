<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Xeno\X as Xeno;
use \Tetraquark\Log as Log;
use \Tetraquark\Contract as Contract;
use \Tetraquark\Block as Block;

class Method extends Block implements Contract\Block
{
    protected array $endChars = [
        '}' => true
    ];

    protected array $instructionEnds = [
        '{' => true,
    ];

    public function objectify(int $start = 0)
    {
        $this->findInstructionEnd($start, 'function', $this->instructionEnds);
        $this->createSubBlocks();
        $this->findAndSetName('function ', ['(' => true]);
        if (\strlen($this->getName()) == 0) {
            $this->setSubtype('anonymous:function');
        }
    }
}
