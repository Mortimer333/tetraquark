<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Xeno\X as Xeno;
use \Tetraquark\Log as Log;
use \Tetraquark\Contract as Contract;
use \Tetraquark\Block as Block;

class Variable extends Block implements Contract\Block
{
    public function objectify(int $start = 0)
    {
        $this->findInstructionEnd($start, $this->subtype, '=');
        for ($i=$this->getCaret(); $i < strlen($this->content); $i++) {
            $letter = $this->content[$i];
            // Is function - somehow check it
            // Is class - new
            // Is normal sequence - ;
        }
    }

    protected function isStartingString(string $letter): bool
    {
        $stringStarts = [
            '"' => '"',
            "'" => "'",
            '`' => '`',
        ];
        return $stringStarts[$letter] ?? false;
    }
}
