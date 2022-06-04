<?php declare(strict_types=1);

namespace Tetraquark;

use \Tetraquark\Contract\{Block as BlockInterface};
use \Tetraquark\Block;
use \Tetraquark\Block\{ScriptBlock as Script, ExportBlock as Export};

/**
 *  Class contains all mapped scripts assigned by their paths
 */
class Folder
{
    protected array $files = [];
    protected string $pointer = '';

    public function __construct()
    {

    }

    public function addFile(string $path, ?Script $script = null, bool $setAsCurrent = false): Script
    {
        if (isset($this->files[$path])) {
            throw new Exception("Script with path ' . $path . ' already exists, can't add it again to the Folder", 500);
        }

        if (\is_null($script)) {
            $script = new Script($path);
        }

        $this->files[$path] = $script;

        if ($setAsCurrent) {
            $this->setCurrentFile($path);
        }
        return $script;
    }

    public function getCurrentFile(): Script
    {
        if (!isset($this->files[$this->pointer])) {
            throw new Exception("No file is curently pointed", 500);
        }
        return $this->files[$this->pointer];
    }

    public function getFile(string $path): Script
    {
        if (!isset($this->files[$path])) {
            throw new Exception("Requested file doesn't exist", 404);
        }
        return $this->files[$path];
    }

    public function setCurrentFile(string $path): self
    {
        if (!isset($this->files[$path])) {
            throw new Exception("No file with passed path exists in this Folder", 500);
        }
        $this->pointer = $path;
        return $this;
    }

    public function fileExists(string $path)
    {
        return isset($this->files[$path]);
    }

    public function findDefaultExport(string $path): BlockInterface
    {
        $script = $this->getFile($path);
        $blocks = $script->getBlocks();
        $export = null;
        for ($i=\sizeof($blocks) - 1; $i >= 0; $i--) {
            $block = $blocks[$i];
            if ($block instanceof Export) {
                $export = $block;
                break;
            }
        }

        if (\is_null($export)) {
            throw new Exception("Export Block not found in " . htmlentities($path), 404);
        }

        $exportDefault = $this->getDefaultBlockFromExport($export);
        if (!($exportDefault instanceof Contract\ExportBlock)) {
            return $exportDefault;
        }
        return $this->matchBlock($script, $exportDefault->getOldName());
    }

    public function matchBlock(BlockInterface $block, string $name): BlockInterface
    {
        foreach ($block->getBlocks() as $block) {
            if ($block->getName() === $name) {
                return $block;
            }
        }
        throw new Exception("Block named " . htmlentities($name) . " not found in given block", 404);
    }

    private function getDefaultBlockFromExport(BlockInterface $export): BlockInterface
    {
        $exportBlocks = $export->getBlocks();
        $firstExportBlock = $exportBlocks[0] ?? throw new Exception("Export Block doesn't have any children", 500);

        if ($firstExportBlock instanceof Block\ExportDefaultBlock) {
            return $exportBlocks[1] ?? throw new Exception("Export Block doesn't have default name added", 500);
        }

        if ($firstExportBlock instanceof Block\ExportObjectBlock) {
            $exportItems = $firstExportBlock->getBlocks();
            foreach ($exportItems as $block) {
                if ($block->getSubType() === Block\ExportObjectItemBlock::DEFAULT_ALIASED) {
                    return $block;
                }
                if ($block->getSubType() === Block\ExportObjectItemBlock::DEFAULT_IMPORTED) {
                    // @TODO
                    throw new Exception("Re-exporting / Aggregating is not currently implemented", 500);
                }
            }
        }

        throw new Exception("Couldn't find default export block", 500);
    }
}
