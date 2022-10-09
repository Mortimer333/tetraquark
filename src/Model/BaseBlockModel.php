<?php declare(strict_types=1);

namespace Tetraquark\Model;

use Tetraquark\Contract\BlockModelInterface;

/**
 * Data model of the single block
 * @codeCoverageIgnore
 */
abstract class BaseBlockModel extends BaseModel implements BlockModelInterface
{
    protected array $children = [];

    public function addChild(BlockModelInterface $block): self
    {
        $this->children[] = $block;
        return $this;
    }

    public function getLastChild(): ?BlockModelInterface
    {
        $size = \sizeof($this->children);
        return $this->children[$size - 1] ?? null;
    }
}
