<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\Log as Log;
use \Tetraquark\Contract as Contract;
use \Tetraquark\Block as Block;

class ArrayBlock extends Block implements Contract\Block
{
    protected array $endChars = [
        ']' => true,
    ];

    public function objectify(int $start = 0)
    {
        $this->setInstruction('');
        $this->setName('');
        Log::log('Start Array: ' . $start);
        $this->setInstructionStart($start);
        $items = [];
        $lastCut = $start;
        $this->createSubBlocks($start + 1);
    }

    public function recreate(): string
    {
        $script = '[';
        foreach ($this->getBlocks() as $block) {
            $trimmed = rtrim(rtrim($block->recreate(), ','), ';');
            if (\mb_strlen($trimmed) > 0) {
                $script .=  $trimmed. ',';
            }
        }
        $script = rtrim($script, ',');
        return $script . '];';
    }
}
