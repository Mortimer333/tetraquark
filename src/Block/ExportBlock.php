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
        $this->setInstruction(self::$content->iCutToContent($start, $end - 1))
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

        return $script . ';';
    }

    public function getExportedBlocks(): array
    {
        $exported = [];
        foreach ($this->getBlocks() as $block) {
            match ($block::class) {
                ExportAllBlock::class => throw new Exception("Exporting from other files in not currently implemented", 500),
                ExportAsBlock::class => $exported[] = $this->getExportBlockReference($block),
                ExportDefaultBlock::class => false,
                ExportFromBlock::class => false,
                ExportObjectBlock::class => $exported = array_merge($exported, $this->getObjectItemsReferences($block)),
                default => $exported[] = $block // any none exportBlock is just regular variable, export it
            };
        }
        return $exported;
    }

    public function getExportBlockReference(ExportAsBlock $block): Contract\Block
    {
        $name = $block->getOldName();
        $script = $this->getScript();
        return self::$folder->matchBlock($script, $name);
    }

    public function getObjectItemsReferences(ExportObjectBlock $object): array
    {
        $blocks = [];
        $script = $this->getScript();
        foreach ($object->getBlocks() as $block) {
            $oldName = $block->getOldName();
            $newName = $block->getNewName();
            $reference = self::$folder->matchBlock($script, $oldName);
            if (strlen($newName) > 0) {
                $reference->setName($newName);
            }
            $blocks[] = $reference;
        }
        return $blocks;
    }
}
