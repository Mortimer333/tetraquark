<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Content};
use \Tetraquark\Foundation\BlockAbstract as Block;

class StringBlock extends Block implements Contract\Block
{
    public function __construct(
        int $start,
        string $instruction,
        protected string $subtype = '',
        protected array  $data  = []
    ) {
        $this->setInstruction(new Content($instruction));
        parent::__construct($start, $subtype, $data);
    }

    public function objectify(int $start = 0)
    {
        $this->setName('');
        $instruction = $this->getInstruction();
        $this->setCaret($start + $instruction->getLength());
        $this->setInstructionStart($start);
    }

    public function recreate(): string
    {
        $script = $this->replaceVariablesWithAliases(
            $this->getInstruction()
        );

        foreach ($this->getBlocks() as $block) {
            $script .= $block->recreate();
        }

        return $script;
    }
}
