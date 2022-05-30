<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate};
use \Tetraquark\Foundation\BlockAbstract as Block;

class ExportAsBlock extends Block implements Contract\Block
{
    public function objectify(int $start = 0)
    {
        $this->setName('');
        list($letter, $namePos) = $this->getPreviousLetter($start - 2, self::$content);
        list($letter, $aliasPos) = $this->getNextLetter($start, self::$content);
        $this->setInstructionStart($properStart);

        $end = $this->findVariableEnd($start);
        $this->setInstruction(self::$content->iCutToContent($start, $end))
            ->setCaret($end)
        ;
        $this->blocks = $this->createSubBlocksWithContent($this->getInstruction()->__toString());
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
