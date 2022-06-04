<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Str};
use \Tetraquark\Foundation\VariableBlockAbstract as VariableBlock;

class ImportBlock extends VariableBlock implements Contract\Block
{
    protected array $importItems = [];

    public function objectify(int $start = 0)
    {
        $this->setName('');
        $properStart = $start - (\mb_strlen("import") + 1);
        $this->setInstructionStart($properStart);

        $end = $this->findVariableEnd($start);
        $this->setInstruction(self::$content->iCutToContent($start, $end))
            ->setCaret($end)
        ;
        $this->blocks = $this->createSubBlocksWithContent(str_replace("\n"," ", $this->getInstruction()->__toString()));
        // If it's just importing global code (import 'module') do nothing
        if ($this->blocks[0] instanceof StringBlock) {
            $globalImport = $this->blocks[0];
            $path = $globalImport->getInstruction()
                ->trim(
                    $globalImport->getInstruction()->__toString()[0] // Trim string landmarks
                )->__toString();

            if (self::$folder->fileExists($path)) {
                $script = self::$folder->getFile($path);
            } else {
                $script = new ScriptBlock($path);
            }

            $imported = '(() => {';
            $imported .= $script->getMinified();
            $imported .= '})();';
            return;
        }
        $from = $this->blocks[\sizeof($this->blocks) - 1];
        if (!($from instanceof ImportFromBlock)) {
            throw new Exception("Couldn't find from block for import instruction.", 500);
        }

        $this->addImportItems($this->blocks);


        $path = $from->getPath();

        if (self::$folder->fileExists($path)) {
            $script = self::$folder->getFile($path);
        } else {
            $script = new ScriptBlock($path);
        }

        $importedGlob = '';
        foreach ($this->importItems['default'] ?? [] as $item) {
            $defaultBlock = self::$folder->findDefaultExport($path);
            $defaultBlock->setName($item->getName());
            $importedGlob .= method_exists($defaultBlock, 'recreateForImport') ? $defaultBlock->recreateForImport() : $defaultBlock->recreate();
        }

        Log::log('Heeeeeeeee: ' . $importedGlob);
    }

    public function recreate(): string
    {
        $script = 'import ';

        foreach ($this->getBlocks() as $block) {
            $script .=  rtrim($block->recreate(), ';');
        }

        return$script . ';';
    }

    protected function addImportItems(array $blocks): void
    {
        foreach ($blocks as $block) {
            match ($block::class) {
                ImportAsBlock        ::class => $this->addImportItem('default', $block),
                ImportAllBlock       ::class => $this->addImportItem('namespace', $block),
                ImportObjectBlock    ::class => $this->addImportItems($block->getBlocks()),
                ExportObjectItemBlock::class => $this->addImportItem('item', $block),
                default                      => '' // do nothin'
            };
        }
    }

    protected function addImportItem(string $type, Contract\Block $block): void
    {
        if (!isset($this->importItems[$type])) {
            $this->importItems[$type] = [];
        }
        $this->importItems[$type][] = $block;
    }
}
