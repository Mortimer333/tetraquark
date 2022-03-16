<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\Contract as Contract;
use \Tetraquark\Block as Block;

class Script extends Block implements Contract\Block
{
    private bool $includeFunctionEnd = false;
    public function objectify(int $start = 0)
    {
        $map  = [];
        $item = [];
        for ($i=$start; $i < \strlen($this->content); $i++) {
            $letter = $this->content[$i];
            if ($name = $this->isNewBlock($letter, $i, $this->content)) {
                $block = $this->blockFactory($name, $this->content, $i);
                if ($block instanceof Method || $block instanceof ArrowMethod) {
                    if ($block->multiLine()) {
                        $this->includeFunctionEnd = true;
                        $this->endChars['}'] = true;
                    }
                }
                $this->blocks[] = $block;
                $this->content = $block->getContent();
            }
            // if ($letter == ' ') {
            //     $this->addWord($item, $this->content, $i);
            //     continue;
            // }
            // if ($this->isEndChar($letter)) {
            //     if ($this->includeFunctionEnd && $letter == '}') {
            //         unset($this->endChars['}']);
            //     }
            //     $this->addWord($item, $this->content, $i);
            //     if (\sizeof($item) > 0) {
            //         $map[] = $item;
            //         $item = [];
            //     }
            // }
        }
        var_dump($this->blocks);
    }
}
