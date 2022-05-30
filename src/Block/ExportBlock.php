<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate};
use \Tetraquark\Foundation\VariableBlockAbstract as VariableBlock;

class ExportBlock extends VariableBlock implements Contract\Block
{
    public const MULTI_LINE = 'multiline';
    public const SINGLE_LINE = 'singleline';
    protected array $endChars = [];

    public function objectify(int $start = 0)
    {
        $this->setName('');
        $properStart = $start - (\mb_strlen("export") + 1);
        list($letter, $pos) = $this->getNextLetter($start - 1, self::$content);
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
