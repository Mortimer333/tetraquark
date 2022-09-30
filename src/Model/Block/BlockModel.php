<?php declare(strict_types=1);

namespace Tetraquark\Model\Block;

use Tetraquark\Model\BaseBlockModel;

use Tetraquark\Contract\BlockModelInterface;

/**
 * Data model of the single block
 */
class BlockModel extends BaseBlockModel
{
    protected ?int $blockStart = null;
    protected ?int $blockEnd = null;

    public function __construct(
        protected int $start,
        protected int $end,
        protected array $landmark,
        protected array $data,
        protected int $index,
        protected ?BlockModelInterface $parent = null,
        protected array $path = [],
        protected array $children = [],
        protected array $comments = [],
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

    public function getEnd(): int
    {
        return $this->end;
    }

    public function setEnd(int $end): self
    {
        $this->end = $end;
        return $this;
    }

    public function getLandmark(): array
    {
        return $this->landmark;
    }

    public function setLandmark(array $landmark): self
    {
        $this->landmark = $landmark;
        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function getIndex(): int
    {
        return $this->index;
    }

    public function setIndex(int $index): self
    {
        $this->index = $index;
        return $this;
    }

    public function getParent(): ?BlockModel
    {
        return $this->parent;
    }

    public function setParent(?BlockModel $parent): self
    {
        $this->parent = $parent;
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

    public function getBlockStart(): ?int
    {
        return $this->blockStart;
    }

    public function setBlockStart(?int $blockStart): self
    {
        $this->blockStart = $blockStart;
        return $this;
    }

    public function getBlockEnd(): ?int
    {
        return $this->blockEnd;
    }

    public function setBlockEnd(?int $blockEnd): self
    {
        $this->blockEnd = $blockEnd;
        return $this;
    }

    public function getPath(): array
    {
        return $this->path;
    }

    public function setPath(array $path): self
    {
        $this->path = $path;
        return $this;
    }
}
