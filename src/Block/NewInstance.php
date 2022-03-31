<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\Exception as Exception;
use \Tetraquark\Log as Log;
use \Tetraquark\Contract as Contract;
use \Tetraquark\MethodBlock as MethodBlock;

class NewInstance extends MethodBlock implements Contract\Block
{
    public function objectify(int $start = 0)
    {
        $searchForClosing = false;
        $nameStarted      = false;
        $nameEnded        = false;
        $end              = null;
        for ($i=$start; $i < \mb_strlen(self::$content); $i++) {
            $letter = self::$content[$i];
            if ($this->isWhitespace($letter)) {
                if ($nameStarted) {
                    $nameEnded = true;
                }
                continue;
            } elseif (!$searchForClosing && $letter == '(') {
                $this->setName(\mb_substr(self::$content, $start, $i - $start));
                $searchForClosing = true;
                continue;
            } elseif ($letter == ';') {
                $end = $i;
                $this->setName(\mb_substr(self::$content, $start, $end - $start));
                break;
            } elseif (!$searchForClosing) {
                // If its non whitespace char and we don't search for ')'
                if ($nameEnded) {
                    $end = $i - 1;
                    $this->setName(\mb_substr(self::$content, $start, $end - $start));
                    break;
                }
                $nameStarted = true;
            } elseif ($searchForClosing && $letter == ')') {
                $end = $i + 1;
                break;
            }
        }

        if (\is_null($end)) {
            throw new Exception('End for new Class not found', 404);
        }

        $this->setCaret($end);

        $instruction = \mb_substr(self::$content, $start, $end - $start);
        $this->setInstruction($instruction)
            ->setInstructionStart($start)
        ;
        $this->findAndSetArguments();
    }

    public function recreate(): string
    {
        $script = 'new ' . $this->getAlias($this->getName());
        $args = $this->getArguments();
        if (\sizeof($args) == 0) {
            return $script . ';';
        }
        return $script . '(' . implode(',', $args) . ');';
    }
}
