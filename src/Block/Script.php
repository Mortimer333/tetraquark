<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\Log as Log;
use \Tetraquark\Contract as Contract;
use \Tetraquark\Block as Block;

class Script extends Block implements Contract\Block
{
    public function objectify(int $start = 0)
    {
        $map    = [];
        $item   = [];
        $scopes = [];
        $scope  = $this;
        $word   = '';
        array_unshift($scopes, $scope);
        for ($i=$start; $i < \strlen($scope->content); $i++) {
            $this->setCaret($i);
            $scope  = $scopes[0];
            $letter = $scope->content[$i];
            $word  .= $letter;
            if ($this->isWhitespace($letter)) {
                $word = '';
            }
            Log::log("Letter: " . $letter, 2);
            if ($letter == '}' && $scope->endFunction) {
                Log::log("Scope change! From " . get_class($scope) . " to " . get_class($scopes[1]), 1);
                array_shift($scopes);
                $scope = $scopes[0];
            }
            if ($name = $this->isNewBlock($word)) {
                Log::increaseIndent();
                Log::log("New block: " . $name);
                $block = $this->blockFactory($name, $scope->content, $i);
                if ($block instanceof Method || $block instanceof ArrowMethod) {

                }
                $scope->blocks[] = $block;
                Log::log('Iteration count changed from ' . $i . " to " . $block->getCaret(), 1);
                $i = $block->getCaret();
                Log::log("Instruction: `". $block->getInstruction() . "`");
                if ($block->endFunction) {
                    Log::log("Block is multiline, adding another layer of scope", 1);
                    $scope = $block;
                    array_unshift($scopes, $scope);
                } else {
                    Log::log("Block is not multiline", 1);
                }
                Log::decreaseIndent();
            }
        }
        Log::log("=======================");
        $this->displayBlocks($this->blocks);
    }

    public function displayBlocks(array $blocks)
    {
        foreach ($blocks as $block) {
            Log::log("Block: " . get_class($block), 1);
            Log::log("Subtype: " . $block->getSubtype(), 1);
            Log::log("Instruction: " . $block->getInstruction());
            Log::log("=======", 1);
            Log::increaseIndent();
            $this->displayBlocks($block->blocks);
            Log::decreaseIndent();
        }
    }
}
