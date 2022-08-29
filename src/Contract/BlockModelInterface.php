<?php declare(strict_types=1);

namespace Tetraquark\Contract;

interface BlockModelInterface
{
    public function getLastChild(): ?BlockModelInterface;
    public function addChild(BlockModelInterface $block): self;
}
