<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate};
use \Tetraquark\Foundation\VariableBlockAbstract as VariableBlock;

class ExportBlock extends VariableBlock implements Contract\Block, Contract\ExportBlock
{
    public function objectify(int $start = 0)
    {
        $this->setName('');
        $properStart = $start - (\mb_strlen("export") + 1);
        $this->setInstructionStart($properStart);

        $end = $this->findVariableEnd($start);
        $this->setInstruction(self::$content->iCutToContent($start, $end))
            ->setCaret($end)
        ;
        $this->blocks = $this->createSubBlocksWithContent(str_replace("\n"," ", $this->getInstruction()->__toString()));
    }

    public function recreate(): string
    {
        $script = 'export ';

        foreach ($this->getBlocks() as $block) {
            $script .=  rtrim($block->recreate(), ';');
        }

        return$script . ';';
    }
}
