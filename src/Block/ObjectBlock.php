<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Content};
use \Tetraquark\Foundation\BlockAbstract as Block;

class ObjectBlock extends Block implements Contract\Block
{
    protected array $endChars = [
        "}" => true
    ];

    public function objectify(int $start = 0)
    {
        $this->setName('');
        $this->setCaret($start + 1);
        $this->setInstruction(new Content(''));
        $this->setInstructionStart($start);
        $this->blocks = array_merge($this->blocks, $this->createSubBlocks($start + 1));
        // Check if last block is not solo
        $lastBlock = $this->blocks[\sizeof($this->blocks) - 1] ?? false;
        if ($lastBlock && $lastBlock instanceof UndefinedBlock) {
            $block = new ObjectSoloValueBlock(
                $lastBlock->getInstructionStart() + $lastBlock->getInstruction()->getLength() - 1,
                ',',
                $this
            );
            $block->setChildIndex(\sizeof($this->blocks));
            $this->blocks[\sizeof($this->blocks) - 1] = $block;
        }
    }

    public function recreate(): string
    {
        $script = '{';

        foreach ($this->getBlocks() as $block) {
            $script .= rtrim(trim($block->recreate()), ';');
        }

        $script = rtrim($script, ',');

        return $script . '};';
    }
}
