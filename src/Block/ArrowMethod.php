<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\Contract as Contract;
use \Tetraquark\Block as Block;

class ArrowMethod extends Block implements Contract\Block
{
    public function objectify(int $start = 0)
    {
        $searchForBracketsStart = false;
        $searchForNotAllowedChar = false;
        $start = null;
        $end   = null;
        for ($i=$start; $i >= 0; $i--) {
            $letter = $this->content[$i];
            if ($letter == ')') {
                $searchForBracketsStart = true;
                continue;
            }
            if ($searchForBracketsStart && $letter == '(') {
                $start = $i;
                break;
            }

            if (preg_match('/[a-zA-Z0-9]/', $letter)) {
                $searchForNotAllowedChar
            }
        }
    }

    public function multiLine(): bool
    {
        return isset($this->instruction) && $this->instruction[strlen($this->instruction) - 1] == '{';
    }
}
