<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate};
use \Tetraquark\Foundation\BlockAbstract as Block;

class ObjectSoloValueBlock extends Block implements Contract\Block
{
    protected array $endChars = [
        "\n" => true,
        " " => true,
        "," => true,
        "{" => true,
    ];

    public function objectify(int $start = 0)
    {
        $this->setName('');
        $this->setCaret($start);
        $this->findInstructionStart($start - 1);
    }

    public function recreate(): string
    {
        return $this->replaceVariablesWithAliases(
            $this->getInstruction() . ","
        );
    }
}
