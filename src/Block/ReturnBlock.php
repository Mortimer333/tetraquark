<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Content};
use \Tetraquark\Foundation\VariableBlockAbstract as VariableBlock;

class ReturnBlock extends VariableBlock implements Contract\Block
{
    protected array $endChars = [
        ';' => true,
    ];
    
    public function objectify(int $start = 0)
    {
        $this->setName('');
        $this->setInstructionStart($start - 6);
        $end = $this->findVariableEnd($start);
        $this->setCaret($end);
        $instr = self::$content->iSubStr($start, $end);
        // $instr = preg_replace('/[\n]/', ' ', $instr);
        // $instr = preg_replace('/[ \t]+/', ' ', $instr) . ';';
        $this->setInstruction(new Content($instr . ';'));
        $this->blocks = $this->createSubBlocksWithContent($instr);
    }

    public function recreate(): string
    {
        $script = 'return ';
        foreach ($this->getBlocks() as $block) {
            $script .= $block->recreate();
        }
        return rtrim($script, ';') . ';';
    }
}
