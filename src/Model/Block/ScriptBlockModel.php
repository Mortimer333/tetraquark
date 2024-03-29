<?php declare(strict_types=1);

namespace Tetraquark\Model\Block;

use Tetraquark\Model\BaseBlockModel;

/**
 * Data model of the single block
 * @codeCoverageIgnore
 */
class ScriptBlockModel extends BaseBlockModel
{
    protected ?int $end = null;

    public function __construct(
        protected array $children = [],
    ) {
    }

    public function getStart(): int
    {
        return 0; // Script always starts at 0
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

    public function getBlockStart(): int
    {
        return 0; // Script always starts at 0
    }

    public function getIsBlock(): bool
    {
        return true;
    }
}
