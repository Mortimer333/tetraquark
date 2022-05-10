<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate};
use \Tetraquark\Foundation\VariableBlockAbstract as VariableBlock;

class SymbolBlock extends VariableBlock implements Contract\Block
{
    public function objectify(int $start = 0)
    {
        Log::log($this->getSubtype());
        $this->setName('');
        // Find symbol start
        $properStart = $start - (\mb_strlen($this->getSubtype()) - 1);
        // for ($i=$start - 1; $i >= 0; $i--) {
        //     $letter = self::$content[$i];
        //     if (!Validate::isSymbol($letter)) {
        //         $properStart = $i + 1;
        //         break;
        //     }
        // }
        // if (\is_null($properStart)) {
        //     $properStart = 0;
        // }

        $this->setInstruction(\mb_substr(self::$content, $properStart, $start - $properStart + 1))
            ->setInstructionStart($properStart)
            ->setCaret($start);
    }

    public function recreate(): string
    {
        return $this->replaceVariablesWithAliases($this->getInstruction());
    }
}
