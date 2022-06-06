<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Str};
use \Tetraquark\Foundation\VariableBlockAbstract;

class ImportBlock extends VariableBlockAbstract implements Contract\Block
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
        // If it's just importing global code (import 'module') get path from string
        if ($this->blocks[0] instanceof StringBlock) {
            $globalImport = $this->blocks[0];
            $path = $globalImport->getInstruction()
                ->trim(
                    $globalImport->getInstruction()->__toString()[0] // Trim string landmarks
                )->__toString();
        } else {
            // else find proper form block
            $from = $this->blocks[\sizeof($this->blocks) - 1];
            if (!($from instanceof ImportFromBlock)) {
                throw new Exception("Couldn't find from block for import instruction.", 500);
            }

            $this->addImportItems($this->blocks);
            $path = $from->getPath();
        }


        if ($path === ScriptBlock::DUMMY_PATH) {
            return;
        }

        if (self::$folder->fileExists($path)) {
            $script = self::$folder->getFile($path);
        } else {
            $script = new ScriptBlock($path);
        }

        $imported = '(()=>{';
        $imported .= $script->recreateSkip([
            ImportBlock::class => true,
            ExportBlock::class => true
        ]);

        // If only import global code then stop here
        if ($this->blocks[0] instanceof StringBlock) {
            $imported .= '})();';
            return;
        }

        ///  !!!!!!!! This is wrong
        ///  Acutally globabl code is alway run (any function usage is always added)
        ///  each time import is added we have to (()=>{[import stuff]}) like above.
        ///  Then we can add those for furthere use.
        ///
        ///  Another important thing is to remember is to somehow make scope of the imported function inside the import. They don't actually use
        ///  current script scope but only that from import. But all methods used in script are hidden from global (current script)
        $namespace = false;
        $deconstructNames = [];
        $importNames = [];
        if (isset($this->importItems['default'])) {
            $default = $this->importItems['default'];
            $defaultBlock = self::$folder->findDefaultExport($path);
            $deconstructNames[] = $default->getName();
            if ($defaultBlock::class === VariableBlock::class) {
                $importNames[] = $defaultBlock->getBlocks()[0]->getName();
            } else {
                $importNames[] = $defaultBlock->getName();
            }
            // $imported .= method_exists($defaultBlock, 'recreateForImport') ? $defaultBlock->recreateForImport() : $defaultBlock->recreate();
        }

        foreach ($this->importItems['items'] as $item) {
            $block = self::$folder->matchBlock($script, $item->getOldName());
            $newName = $item->getNewName();
            if (strlen($newName) > 0) {
                $block->setName($newName);
                $importNames[] = $newName;
            } else {
                $importNames[] = $block->getName();
            }
            $deconstructNames[] = $block->getName();
            // $imported .= $block->recreate();
        }

        if (isset($this->importItems['namespace'])) {
            $namespace = $this->importItems['namespace']->getNewName();
            $export = self::$folder->findExportInScript($path);
            $exportedBlocks = $export->getExportedBlocks();
            // $imported .= 'const ' . $namespace->getNewName() . '={};';
            foreach ($exportedBlocks as $block) {
                if ($block::class === VariableBlock::class) {
                    foreach ($block->getBlocks() as $subBlock) {
                        if (!in_array($subBlock->getName(), $importNames)) {
                            $importNames[] = $subBlock->getName();
                        }
                    }
                } else {
                    if (!in_array($block->getName(), $importNames)) {
                        $importNames[] = $block->getName();
                    }
                }
                // $imported .= $namespace->getNewName() . '.' . (method_exists($block, 'recreateForImport') ? $block->recreateForImport() : $block->recreate());
            }
        }
        if (sizeof($deconstructNames) > 0) {
            $importedPrefix = 'const {';
            foreach ($deconstructNames as $name) {
                $importedPrefix .= $name. ',';
            }

            $importedPrefix = rtrim($importedPrefix, ',') . '}=';
        } else {
            $importedPrefix = 'const ';
        }

        if ($namespace) {
            $importedPrefix .= $namespace . '=';
        }

        $imported = $importedPrefix . $imported;
        $importedSufix = 'return {';
        foreach ($importNames as $name) {
            $importedSufix .= $name. ',';
        }

        $imported .= rtrim($importedSufix, ',') . '};})();';
        Log::log('Heeeeeeeee: ' . $imported);
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
