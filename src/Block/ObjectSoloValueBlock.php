<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log as Log, Exception as Exception, Contract as Contract, Validate as Validate};
use \Tetraquark\Abstract\BlockAbstract as Block;

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
