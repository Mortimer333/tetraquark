<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log as Log, Exception as Exception, Contract as Contract, Validate as Validate};
use \Tetraquark\Foundation\CommentBlockAbstract as CommentBlock;

class MultiCommentBlock extends CommentBlock implements Contract\Block
{
    protected array $endChars = [
        "\n" => true
    ];

    public function objectify(int $start = 0)
    {
        $this->setName('');
        $previousLetter = self::$content[$start - 1];
        $end = null;
        for ($i=$start + 2; $i < \mb_strlen(self::$content); $i++) {
            $letter = self::$content[$i];
            if ($letter == "/" && $previousLetter == "*") {
                $end = $i + 1;
                break;
            }

            $previousLetter = $letter;
        }

        if (\is_null($end)) {
            $end = \mb_strlen(self::$content) - 1 - $start;
        }

        $this->setInstruction(\mb_substr(self::$content, $start - 1, $end - ($start - 1)));
        $this->setInstructionStart($start - 1);
        $this->setCaret($end);
    }
}
