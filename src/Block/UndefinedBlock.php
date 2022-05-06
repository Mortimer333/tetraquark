<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log as Log, Exception as Exception, Contract as Contract, Validate as Validate};
use \Tetraquark\Foundation\BlockAbstract as Block;

class UndefinedBlock extends Block implements Contract\Block
{
    public function __construct(
        int $start,
        protected string $instruction,
        protected string $subtype = '',
        protected array  $data  = []
    ) {
        parent::__construct($start, $subtype, $data);
    }

    public function objectify(int $start = 0)
    {
        $this->setName('');
        $instruction = $this->getInstruction();
        $this->setCaret($start + \mb_strlen($instruction));
        $this->setInstructionStart($start)
            ->setInstruction($instruction)
        ;
    }

    public function recreate(): string
    {
        $script = $this->replaceVariablesWithAliases(
            $this->getInstruction()
        );

        foreach ($this->getBlocks() as $block) {
            $script .= $block->recreate();
        }

        return trim($script) . ' ';
    }
}
