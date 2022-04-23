<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\Log as Log;
use \Tetraquark\Contract as Contract;
use \Tetraquark\Abstract\BlockAbstract as Block;

class StringBlock extends Block implements Contract\Block
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
        $this->setInstructionStart($start)
            ->setInstruction($instruction)
        ;
    }

    public function recreate(): string
    {
        $script = $this->removeAdditionalSpaces(
            $this->replaceVariablesWithAliases(
                $this->getInstruction()
            )
        );

        foreach ($this->getBlocks() as $block) {
            $script .= $block->recreate();
        }

        return $script;
    }
}
