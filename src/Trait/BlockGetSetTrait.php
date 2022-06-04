<?php declare(strict_types=1);

namespace Tetraquark\Trait;

use \Tetraquark\{Log, Content};
use \Tetraquark\Foundation\BlockAbstract as Block;
use \Tetraquark\Contract\{Block as BlockInterface};

trait BlockGetSetTrait
{
    public function getContent(): Content
    {
        return self::$content;
    }

    public function addContent(Content $content): self
    {
        self::$content = $content;
        return $this;
    }

    public function getCaret(): int
    {
        return $this->caret;
    }

    public function setCaret(int $caret): self
    {
        $this->caret = $caret;
        return $this;
    }

    public function getSubtype(): string
    {
        return $this->subtype;
    }

    public function setSubtype(string $subtype): self
    {
        $this->subtype = trim($subtype);
        return $this;
    }

    public function getBlocks(): array
    {
        return $this->blocks;
    }

    public function setBlocks(array $blocks): self
    {
        $this->blocks = $blocks;
        return $this;
    }

    public function getAlias(string $name): string
    {
        return self::$mappedAliases[$name] ?? $name;
    }

    public function setAlias(string $name, string $alias): self
    {
        self::$mappedAliases[$name] = $alias;
        return $this;
    }

    public function getInstruction(): Content
    {
        return $this->instruction;
    }

    public function setInstruction(Content $instruction): self
    {
        $this->instruction = $instruction;
        return $this;
    }

    public function setInstructionStart(int $start): self
    {
        $this->instructionStart = $start;
        return $this;
    }

    public function getInstructionStart(): int
    {
        return $this->instructionStart;
    }

    public function setName(string $name): self
    {
        $this->name = trim($name);
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setChildIndex(int $childIndex): void
    {
        $this->childIndex = $childIndex;
    }

    public function getChildIndex(): int
    {
        return $this->childIndex;
    }

    public function setParent(BlockInterface $parent): void
    {
        $this->parent = $parent;
    }

    public function getParent(): ?BlockInterface
    {
        return $this->parent;
    }
}
