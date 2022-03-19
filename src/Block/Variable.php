<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Xeno\X as Xeno;
use \Tetraquark\Log as Log;
use \Tetraquark\Contract as Contract;
use \Tetraquark\Block as Block;

class Variable extends Block implements Contract\Block
{
    protected array $endChars = [
        ';' => true,
        "\n" => true
    ];

    protected array $instructionEnds = [
        '=' => true,
    ];

    public function objectify(int $start = 0)
    {
        $this->findInstructionEnd($start, $this->subtype, $this->instructionEnds);
        $this->createSubBlocks();

        // $word = '';
        // for ($i=$this->getCaret(); $i < strlen(self::$content); $i++) {
        //     $letter = self::$content[$i];
        //     // Is function - somehow check it
        //     // Is class - new
        //     // Is normal sequence - ;
        //     $word .= $letter;
        //     if ($this->isWhitespace($letter)) {
        //         $word = '';
        //     }
        //     $this->constructBlock($word, $i);
        // }
    }

    protected function isStartingString(string $letter): bool
    {
        $stringStarts = [
            '"' => '"',
            "'" => "'",
            '`' => '`',
        ];
        return $stringStarts[$letter] ?? false;
    }
}
