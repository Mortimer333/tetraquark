<?php declare(strict_types=1);

namespace Tetraquark\Foundation;
use \Tetraquark\{Exception as Exception, Block as Block, Log as Log, Validate as Validate};

abstract class VariableBlockAbstract extends BlockAbstract
{
    public function recreate(): string
    {
        $script = $this->getSubType() . ' ';

        foreach ($this->getBlocks() as $i => $block) {
            if ($i == 0) {
                $script .= $block->recreate();
            } else {
                $script .= ',' . $block->recreate();
            }
        }

        $scriptLastLetter = $script[\mb_strlen($script) - 1];
        $addSemiColon = [
            ';' => false,
            ',' => false
        ];

        if ($addSemiColon[$scriptLastLetter] ?? true) {
            $script .= ';';
        }
        return $script;
    }

    protected function findVariableEnd(int $start): int
    {
        $end = null;
        $lastLetter = '';
        for ($i=$start + 1; $i < \mb_strlen(self::$content); $i++) {
            $letter = self::$content[$i];
            if ($letter === ' ') {
                continue;
            }

            list($letter, $i) = $this->skipIfNeccessary(self::$content, $letter, $i);
            list($lastLetter) = $this->getPreviousLetter($i - 1, self::$content);

            if ($letter === ';') {
                $end = $i;
                break;
            }

            if ($letter === "\n") {
                if (Validate::isOperator($lastLetter)) {
                    continue;
                }

                list($nextLetter) = $this->getNextLetter($i, self::$content);
                if (Validate::isOperator($nextLetter)) {
                    continue;
                }

                $end = $i;
                break;
            }

        }

        return $end ?? \mb_strlen(self::$content);
    }

    protected  function seperateVariableItems(int $start, int $end): array
    {
        $itemStart = $start;
        $items = [];
        for ($i=$start; $i < \mb_strlen(self::$content); $i++) {
            if ($i == $end) {
                break;
            }

            $letter = self::$content[$i];
            if (Validate::isWhitespace($letter)) {
                continue;
            }

            list($letter, $i) = $this->skipIfNeccessary(self::$content, $letter, $i);

            if ($i >= $end) {
                break;
            }

            if ($letter === ',') {
                $items[] = \mb_substr(self::$content, $itemStart, $i - $itemStart);
                $itemStart = $i + 1;
                continue;
            }
        }

        $lastItem = \mb_substr(self::$content, $itemStart, $end - $itemStart);
        if (\mb_strlen($lastItem) > 0) {
            $items[] = $lastItem;
        }

        return $items;
    }
}