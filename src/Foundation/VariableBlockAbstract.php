<?php declare(strict_types=1);

namespace Tetraquark\Foundation;
use \Tetraquark\{Exception, Block, Log, Validate};

abstract class VariableBlockAbstract extends BlockAbstract
{
    public function recreate(): string
    {
        $script = $this->getSubType() . ' ';

        foreach ($this->getBlocks() as $i => $block) {
            if ($i == 0) {
                $script .= rtrim(rtrim($block->recreate(), ';'), ',');
            } else {
                $script .= ',' . rtrim(rtrim($block->recreate(), ';'), ',');
            }
        }

        return $script . ';';
    }

    public function recreateForImport(): string
    {
        $script = '';

        if (\sizeof($this->getBlocks()) !== 1) {
            throw new Exception('Variable recreate for import should only/at least have one child', 500);
        }

        return rtrim(rtrim($this->getBlocks()[0]->recreate(), ';'), ',') . ';';
    }

    protected function findVariableEnd(int $start): int
    {
        $end = null;
        $lastLetter = '';
        for ($i=$start + 1; $i < self::$content->getLength(); $i++) {
            $letter = self::$content->getLetter($i);
            if ($letter === ' ') {
                continue;
            }

            list($letter, $i) = $this->skipIfNeccessary(self::$content, $letter, $i);
            list($lastLetter, $lastPos) = $this->getPreviousLetter($i - 1, self::$content);

            if ($letter === ';') {
                $end = $i;
                break;
            }

            if ($letter === "\n") {
                if (
                    Validate::isOperator($lastLetter)
                    && !Validate::isStringLandmark($lastLetter, '')
                    && !Validate::isComment($lastPos, self::$content)
                ) {
                    continue;
                }

                list($nextLetter, $nextPos) = $this->getNextLetter($i, self::$content);
                if (
                    Validate::isOperator($nextLetter)
                    && !Validate::isStringLandmark($nextLetter, '')
                    && !Validate::isComment($nextPos, self::$content)
                ) {
                    continue;
                }

                list($previousWord) = $this->getPreviousWord($i, self::$content);
                if (Validate::isExtendingKeyWord($previousWord)) {
                    continue;
                }

                list($nextWord) = $this->getNextWord($i, self::$content);
                if (Validate::isExtendingKeyWord($nextWord)) {
                    continue;
                }

                $end = $i;
                break;
            }

        }

        return $end ?? self::$content->getLength();
    }

    protected  function seperateVariableItems(int $start, int $end): array
    {
        $itemStart = $start;
        $items = [];
        for ($i=$start; $i < self::$content->getLength(); $i++) {
            if ($i == $end) {
                break;
            }

            $letter = self::$content->getLetter($i);
            if (Validate::isWhitespace($letter)) {
                continue;
            }

            list($letter, $i) = $this->skipIfNeccessary(self::$content, $letter, $i);

            if ($i >= $end) {
                break;
            }

            if ($letter === ',') {
                $items[] = self::$content->iSubStr($itemStart, $i - 1);
                $itemStart = $i + 1;
                continue;
            }
        }

        $lastItem = self::$content->iSubStr($itemStart, $end - 1);
        if (\mb_strlen($lastItem) > 0) {
            $items[] = $lastItem;
        }

        return $items;
    }

    protected function addVariableItems(array $items): void
    {
        foreach ($items as $item) {
            // $item = preg_replace('/[\n]/', ' ', $item);
            // $item = preg_replace('/[ \t]+/', ' ', $item) . ';';
            self::$content->addContent($item . ';');
            $variable = new Block\VariableItemBlock(parent: $this);
            $variable->setChildIndex(\sizeof($this->blocks));
            $this->blocks[] = $variable;
            self::$content->removeContent();
        }
    }
}
