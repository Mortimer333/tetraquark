<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Content};
use \Tetraquark\Foundation\BlockAbstract as Block;

class ExportAsBlock extends Block implements Contract\Block, Contract\ExportBlock
{
    protected string $oldName = '';

    public function __construct(
        int $start,
        string $instruction,
        protected string $subtype = '',
        protected ?Contract\Block $parent = null,
    ) {
        $this->setInstruction(new Content($instruction))
            ->setOldName($this->getInstruction()->trim()->__toString())
        ;
        parent::__construct($start, $subtype, $parent);
    }

    public function objectify(int $start = 0)
    {
        $instruction = $this->getInstruction();
        $this->setCaret($start + $instruction->getLength());
        $this->setInstructionStart($start);
    }

    public function recreate(): string
    {
        $script = $this->replaceVariablesWithAliases(
            $this->getInstruction()->trim()
        );

        foreach ($this->getBlocks() as $block) {
            $script .= $block->recreate();
        }

        return trim($script) . ' ';
    }

    public function getOldName(): string
    {
        return $this->oldName;
    }

    protected function setOldName(string $oldName): self
    {
        $this->oldName = $oldName;
        return $this;
    }
}
