<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Content};
use \Tetraquark\Foundation\BlockAbstract as Block;

class ImportObjectItemBlock extends Block implements Contract\Block
{
    public const ALIASED = 'aliased';
    PUBLIC CONST DEFAULT_ALIASED = 'default aliased';
    protected array $endChars = [
        "," => true,
        "{" => true,
    ];
    protected string $newName = '';
    protected string $oldName = '';

    public function objectify(int $start = 0)
    {
        if (self::$content->getLetter($start) === ',') {
            $this->findInstructionStart($start);
        } else {
            $this->findInstructionStart($start + 1);
        }
        $instr = $this->getInstruction()->trim()->__toString();
        $parted = explode(' ', $instr);
        if ($parted[1] ?? '' == 'as') {
            $this->setSubtype(self::ALIASED);
            $this->setOldName($parted[0]);
            $this->setNewName($parted[2]);

            if ($parted[2] === 'default') {
                $this->setSubtype(self::DEFAULT_ALIASED);
            }
        } else {
            $this->setOldName($instr);
        }
        $this->setCaret($start + 1);
    }

    public function getNewName(): string
    {
        return $this->newName;
    }

    protected function setNewName(string $newName): self
    {
        $this->newName = trim($newName);
        return $this;
    }

    public function getOldName(): string
    {
        return $this->oldName;
    }

    protected function setOldName(string $oldName): self
    {
        $this->oldName = trim($oldName);
        return $this;
    }

    public function recreate(): string
    {
        return $this->getInstruction() . ",";
    }
}
