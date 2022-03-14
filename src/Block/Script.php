<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\Contract as Contract;
use \Tetraquark\Block as Block;

class Script extends Block implements Contract\Block
{
    public function objectify()
    {
        $map  = [];
        $item = [];
        for ($i=0; $i < \strlen($this->content); $i++) {
            $letter = $this->content[$i];
            if ($name = $this->isNewBlock($letter, $i, $this->content)) {
                $block = $this->blockFactory($name, $this->content);
                $this->blocks[] = $block;
                $this->content = $block->getContent();
            }
            if ($letter == ' ') {
                $this->addWord($item, $this->content, $i);
                continue;
            }
            if ($this->isEndChar($letter)) {
                $this->addWord($item, $this->content, $i);
                if (\sizeof($item) > 0) {
                    $map[] = $item;
                    $item = [];
                }
            }
        }
        var_dump($this->blocks);
    }
}
