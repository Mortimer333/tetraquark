<?php declare(strict_types=1);

namespace Tetraquark\Model;

/**
 * Data model of the single block
 */
class BlockModel extends BaseModel
{
    public function __construct(
        protected int $start,
        protected int $end,
        protected array $landmark,
        protected array $data,
        protected int $index,
        protected ?BlockModel $parent = null,
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

    public function toArray(): array
    {
        $vars = get_class_vars(get_class($this));
        $array = [];
        foreach ($vars as $key => $value) {
            $array[$key] = $this->$key;
        }
        return $array;
    }
}
