<?php declare(strict_types=1);

namespace Tetraquark\Model;

use Tetraquark\{Str, Validate, Exception};

class SettingsModel extends BaseModel
{
    public function __construct(protected int $skip = 0)
    {
    }

    public function increaseSkip(): self
    {
        $this->skip++;
        return $this;
    }

    public function decreaseSkip(): self
    {
        $this->skip--;
        return $this;
    }

    public function getSkip(): int
    {
        return $this->skip;
    }

    public function setSkip(int $skip): self
    {
        $this->skip = $skip;
        return $this;
    }
}
