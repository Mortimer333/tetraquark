<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\Exception as Exception;
use \Tetraquark\Log as Log;
use \Tetraquark\Contract as Contract;
use \Tetraquark\CommentBlock as CommentBlock;

class SingleCommentBlock extends CommentBlock implements Contract\Block
{
    protected array $endChars = [
        "\n" => true
    ];

    public function objectify(int $start = 0)
    {
        $this->setName('');
        $this->findInstructionEnd($start, '');
        $this->setCaret($this->getCaret() - 1);
    }
}