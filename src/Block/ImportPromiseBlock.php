<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Content};
use \Tetraquark\Foundation\BlockAbstract as Block;

class ImportPromiseBlock extends Block implements Contract\Block
{
    protected array $endChars = [
        ')' => true,
    ];

    public function objectify(int $start = 0)
    {
        $current = self::$content->getLetter($start);
        $properStart = $start - (\mb_strlen("import") + 1);
        if ($current != '(') {
            list($letter, $pos) = $this->getNextLetter($start, self::$content);
            if ($letter != '(') {
                throw new Exception('Import promis is missing parenthesis', 404);
            }
            $start = $pos;
        }
        $this->findInstructionEnd($start, "import ");
    }

    public function recreate(): string
    {
        return $this->getInstruction()->__toString();
    }
}
