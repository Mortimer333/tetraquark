<?php declare(strict_types=1);

namespace Tetraquark\Abstract;
use \Tetraquark\{Exception as Exception, Block as Block, Log as Log};

abstract class CommentBlockAbstract extends BlockAbstract
{
    public function recreate(): string
    {
        return '';
    }
}
