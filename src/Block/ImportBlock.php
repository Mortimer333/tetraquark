<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Str};
use \Tetraquark\Foundation\{VariableBlockAbstract, MethodBlockAbstract};

class ImportBlock extends VariableBlockAbstract implements Contract\Block
{
    protected array $importItems = [
        "items" => []
    ];
    protected string $imported;

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
        $path = $this->findPathInBlocks();
        $pathToScript = $this->getScript()->getPath();

        if ($path === ScriptBlock::DUMMY_PATH) {
            return;
        }

        $path = self::$folder->makePathAbsolute($path, dirname($pathToScript));
        if (self::$import->scriptExists($path)) {
            return;
        }

        if (self::$folder->fileExists($path)) {
            $script = self::$folder->getFile($path);
        } else {
            $script = new ScriptBlock($path);
        }

        $imported = $script->recreateSkip([
            ImportBlock::class => true,
            ExportBlock::class => true,
        ]);

        // If only import global code then stop here
        if ($this->blocks[0] instanceof StringBlock) {
            self::$import->setScript($path, $imported);
            return;
        }

        list($deconstructNames, $importNames) = $this->tryAddDefault([], [], $path);
        list($deconstructNames, $importNames) = $this->tryAddItems($deconstructNames, $importNames, $script);
        list($deconstructNames, $importNames, $namespace) = $this->tryAddNamespace($deconstructNames, $importNames, $path);
        $this->transformImport($imported, $path, $deconstructNames, $importNames, $namespace);
    }

    protected function transformImport(string $imported, string $path, array $deconstructNames, array $importNames, bool|string $namespace): void
    {
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

        $importedSufix = 'return {';
        foreach ($importNames as $import) {
            $importedSufix .= $import["name"];
            if (isset($import["value"])) {
                $importedSufix .= ":" . $import["value"];
            }
            $importedSufix .= ',';
        }

        self::$import->setScript($path, $imported . rtrim($importedSufix, ',') . '}');
        $script = $this->getScript();
        self::$import->addRetrival($script->getPath(), $path, $importedPrefix);
    }

    protected function tryAddNamespace(array $deconstructNames, array $importNames, string $path): array
    {
        $namespace = false;
        if (isset($this->importItems['namespace'])) {
            $namespace = $this->importItems['namespace']->getNewName();
            $export = self::$folder->findExportInScript($path);
            $exportedBlocks = $export->getExportedBlocks();
            foreach ($exportedBlocks as $block) {
                if ($block::class === VariableBlock::class) {
                    foreach ($block->getBlocks() as $subBlock) {
                        if (!in_array($subBlock->getName(), $importNames)) {
                            $importNames[] = ["name" => $subBlock->getName()];
                        }
                    }
                } else {
                    if (!in_array($block->getName(), $importNames)) {
                        $importNames[] = ["name" => $block->getName()];
                    }
                }
            }
        }
        return [$deconstructNames, $importNames, $namespace];
    }
    protected function tryAddItems(array $deconstructNames, array $importNames, ScriptBlock $script): array
    {
        foreach ($this->importItems['items'] as $item) {
            $block = self::$folder->matchBlock($script, $item->getOldName());
            $newName = $item->getNewName();
            if (strlen($newName) > 0) {
                $block->setName($newName);
                $importNames[] = ["name" => $newName];
            } else {
                $importNames[] = ["name" => $block->getName()];
            }
            $deconstructNames[] = $block->getName();
        }
        return [$deconstructNames, $importNames];
    }
    protected function tryAddDefault(array $deconstructNames, array $importNames, string $path): array
    {
        if (isset($this->importItems['default'])) {
            $default = $this->importItems['default'];
            $defaultBlock = self::$folder->findDefaultExport($path);
            if ($defaultBlock instanceof VariableBlockAbstract) {
                if ($defaultBlock::class === VariableBlock::class) {
                    $name = $defaultBlock->getBlocks()[0]->getName();
                } else {
                    $name = $defaultBlock->getName();
                }

                $import = ["name" => $name,];
                if ($defaultBlock::class === AttributeBlock::class) {
                    $import["value"] = $defaultBlock->recreateForImport();
                }

                $importNames[] = $import;
            } elseif ($defaultBlock instanceof MethodBlockAbstract && $defaultBlock->getSubType() === FunctionBlock::ANONYMOUS) {
                $importNames[] = [
                    "name" => $defaultBlock->getName(),
                    "value" =>  $defaultBlock->recreateForImport()
                ];
            } else {
                $importNames[] = ["name" => $defaultBlock->getName()];
            }
            $lastName = $importNames[sizeof($importNames) - 1]["name"];
            if ($lastName !== $default->getName()) {
                $deconstructNames[] = $importNames[sizeof($importNames) - 1]["name"] . ':' . $default->getName();
            } else {
                $deconstructNames[] = $lastName;
            }
        }
        return [$deconstructNames, $importNames];
    }

    protected function findPathInBlocks(): string
    {
        if ($this->blocks[0] instanceof StringBlock) {
            $globalImport = $this->blocks[0];
            return $globalImport->getInstruction()
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
            return $from->getPath();
        }
    }

    public function recreate(): string
    {
        if (self::$settings['single_file'] ?? false === true) {
            return '';
        }

        $script = 'import ';

        foreach ($this->getBlocks() as $block) {
            $script .=  rtrim($block->recreate(), ';');
        }

        return $script . ';';
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
