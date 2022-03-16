<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\Contract as Contract;
use \Tetraquark\Block as Block;

class Method extends Block implements Contract\Block
{
    public function objectify(int $start = 0)
    {
        for ($i=$start; $i < strlen($this->content); $i++) {
            $letter = $this->content[$i];
            if ($letter == '{') {
                $this->instruction = substr($this->content, 0, $i + 1);
                $this->content = substr($this->content, $i + 1);
                break;
            }
        }
    }

    public function multiLine(): bool
    {
        return true;
    }
}
