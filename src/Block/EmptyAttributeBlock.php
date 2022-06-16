<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Content};
use \Tetraquark\Foundation\BlockAbstract as Block;
use \Tetraquark\Contract\{Block as BlockInterface};

class EmptyAttributeBlock extends Block implements Contract\Block
{
    protected bool $private = false;
    public function __construct(
        int $start,
        string $instruction,
        protected string $subtype = '',
        protected ?BlockInterface $parent = null,
    ) {
        $this->setInstruction(new Content($instruction));
        parent::__construct($start, $subtype, $parent);
    }

    public function objectify(int $start = 0)
    {
        $possibleName = trim(rtrim($this->getInstruction()->trim()->__toString(), ';'));
        if (Validate::isValidVariable($possibleName)) {
            $this->setName($possibleName);
        }
        if ($this->getParent() instanceof ClassBlock && $possibleName[0] === '#') {
            $this->setPrivate(true);
        }
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

        return trim(trim($script), ';') . ';';
    }

    protected function setPrivate(bool $isPrivate): self
    {
        $this->private = $isPrivate;
        return $this;
    }

    public function isPrivate(): bool
    {
        return $this->private;
    }
}
