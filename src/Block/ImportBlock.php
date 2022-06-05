<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Str};
use \Tetraquark\Foundation\VariableBlockAbstract as VariableBlock;

class ImportBlock extends VariableBlock implements Contract\Block
{
    protected array $importItems = [
        "items" => []
    ];

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
        if ($path === ScriptBlock::DUMMY_PATH) {
            return;
        }

        if (self::$folder->fileExists($path)) {
            $script = self::$folder->getFile($path);
        } else {
            $script = new ScriptBlock($path);
        }
        ///  !!!!!!!! This is wrong
        ///  Acutally globabl code is alway run (any function usage is always added)
        ///  each time import is added we have to (()=>{[import stuff]}) like above.
        ///  Then we can add those to furthere use.
        ///
        ///  Another important thing is to remember is to somehow make scope of the imported function inside the import. They don't actually use
        ///  current script scope but only that from import. But all methods used in script are hidden from global (current script)
        $importedGlob = '';
        if (isset($this->importItems['default'])) {
            $default = $this->importItems['default'];
            $defaultBlock = self::$folder->findDefaultExport($path);
            $defaultBlock->setName($default->getName());
            $importedGlob .= method_exists($defaultBlock, 'recreateForImport') ? $defaultBlock->recreateForImport() : $defaultBlock->recreate();
        }

        if (isset($this->importItems['namespace'])) {
            $namespace = $this->importItems['namespace'];
            $export = self::$folder->findExportInScript($path);
            $exportedBlocks = $export->getExportedBlocks();
            $importedGlob .= 'const ' . $namespace->getNewName() . '={};';
            foreach ($exportedBlocks as $block) {
                $importedGlob .= $namespace->getNewName() . '.' . (method_exists($block, 'recreateForImport') ? $block->recreateForImport() : $block->recreate());
            }
        }

        foreach ($this->importItems['items'] as $item) {
            $block = self::$folder->matchBlock($script, $item->getOldName());
            $newName = $item->getNewName();
            if (strlen($newName) > 0) {
                $block->setName($newName);
            }
            $importedGlob .= $block->recreate();
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
                ExportObjectItemBlock::class => $this->addImportItem('items', $block),
                default                      => '' // do nothin'
            };
        }
    }

    protected function addImportItem(string $type, Contract\Block $block): void
    {
        if ($type === 'items') {
            $this->importItems['items'][] = $block;
            return;
        }
        $this->importItems[$type] = $block;
    }
}
