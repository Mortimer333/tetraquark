<?php declare(strict_types=1);
namespace Tetraquark\Contract;

interface Block
{
    public function objectify(int $start = 0);
    public function recreate(): string;
}
