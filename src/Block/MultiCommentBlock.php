<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate};
use \Tetraquark\Foundation\CommentBlockAbstract as CommentBlock;

class MultiCommentBlock extends CommentBlock implements Contract\Block
{
    protected array $endChars = [
        "\n" => true
    ];

    public function objectify(int $start = 0)
    {
        $this->setName('');
        $previousLetter = self::$content->getLetter($start - 1);
        $end = null;
        for ($i=$start + 2; $i < self::$content->getLength(); $i++) {
            $letter = self::$content->getLetter($i);
            if ($letter == "/" && $previousLetter == "*") {
                $end = $i + 1;
                break;
            }

            $previousLetter = $letter;
        }

        if (\is_null($end)) {
            $end = self::$content->getLength() - 1 - $start;
        }

        $this->setInstruction(self::$content->iCutToContent($start - 1, $end - 1))
            ->setInstructionStart($start - 1)
            ->setCaret($end)
        ;
    }
}
