<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate};
use \Tetraquark\Foundation\CommentBlockAbstract as CommentBlock;

class SingleCommentBlock extends CommentBlock implements Contract\Block
{
    protected array $endChars = [
        "\n" => true
    ];

    public function objectify(int $start = 0)
    {
        $this->setName('');
        $this->findInstructionEnd($start, '', skipString: false);
        $this->setInstructionStart($start - 1);
        $this->setCaret($this->getCaret() - 1);
    }
}
