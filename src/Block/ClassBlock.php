<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate};
use \Tetraquark\Foundation\BlockAbstract as Block;

class ClassBlock extends Block implements Contract\Block
{
    protected array $endChars = [
        '}' => true
    ];

    protected array $instructionEnds = [
        '{' => true,
    ];

    protected ?string $extendsClass = null;

    public function objectify(int $start = 0)
    {
        $this->findInstructionEnd($start, 'class', $this->instructionEnds);
        $this->blocks = array_merge($this->blocks, $this->createSubBlocks($this->getCaret() + 1));
        $this->findAndSetName('class ', $this->instructionEnds);
        $exSegments = explode('extends', $this->getName());
        if (\sizeof($exSegments) > 1) {
            if (\sizeof($exSegments) > 2) {
                throw new Exception('To many extends on class ' . htmlspecialchars($exSegments[0]), 400);
            }
            $this->setName($exSegments[0]);
            $this->setExtendClass($exSegments[1]);
        }
    }

    public function recreate(): string
    {
        $script = 'class ' . $this->getAlias($this->getName());
        if (!\is_null($this->extendsClass)) {
            $script .= ' extends ' . $this->extendsClass;
        }
        $script .= '{';
        foreach ($this->getBlocks() as $block) {
            $script .= $block->recreate();
        }
        return $script . '}';
    }

    public function recreateForImport(): string
    {
        $script = $this->getAlias($this->getName()) . '=class';
        if (!\is_null($this->extendsClass)) {
            $script .= ' extends ' . $this->extendsClass;
        }
        $script .= '{';
        foreach ($this->getBlocks() as $block) {
            $script .= $block->recreate();
        }
        return $script . '}';
    }

    protected function setExtendClass(string $class): self
    {
        $this->extendsClass = trim($class);
        return $this;
    }

    public function getExtendClass(): ?string
    {
        return $this->extendsClass;
    }
}
