<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\Exception as Exception;
use \Tetraquark\Log as Log;
use \Tetraquark\Contract as Contract;
use \Tetraquark\CommentBlock as CommentBlock;

class MultiCommentBlock extends CommentBlock implements Contract\Block
{
    protected array $endChars = [
        "\n" => true
    ];

    public function objectify(int $start = 0)
    {
        $this->setName('');
        $previousLetter = self::$content[$start];
        $end = null;
        for ($i=$start + 1; $i < \mb_strlen(self::$content); $i++) {
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
