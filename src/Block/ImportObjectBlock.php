<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Content};
use \Tetraquark\Foundation\BlockAbstract as Block;

class ImportObjectBlock extends Block implements Contract\Block
{
    protected array $endChars = [
        "}" => true
    ];

    public function objectify(int $start = 0)
    {
        $this->setName('')
            ->setCaret($start + 1)
            ->setInstruction(new Content(''))
            ->setInstructionStart($start)
        ;
        $this->blocks = array_merge($this->blocks, $this->createSubBlocks($start + 1));
        // Check if last block is not solo
        $lastBlock = $this->blocks[\sizeof($this->blocks) - 1] ?? false;
        if ($lastBlock && $lastBlock instanceof UndefinedBlock) {
            $this->blocks[\sizeof($this->blocks) - 1] = new ExportObjectItemBlock(
                $lastBlock->getInstructionStart() + $lastBlock->getInstruction()->getLength() - 1,
                ',',
                $this
            );
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
