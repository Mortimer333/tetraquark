<?php declare(strict_types=1);

namespace Tetraquark\Model\Block;

use Tetraquark\Model\BaseBlockModel;

/**
 * Data model of the single block
 */
class ScriptBlockModel extends BaseBlockModel
{
    protected int $start = 0;
    protected ?int $end = null;

    public function __construct(
        protected array $children = [],
    ) {
    }

    public function getStart(): int
    {
        return $this->start;
    }

    public function setStart(int $start): self
    {
        $this->start = $start;
        return $this;
    }

    public function getEnd(): ?int
    {
        return $this->end;
    }

    public function setEnd(int $end): self
    {
        $this->end = $end;
        return $this;
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function setChildren(array $children): self
    {
        $this->children = $children;
        return $this;
    }
}
