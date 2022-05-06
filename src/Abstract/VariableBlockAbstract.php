<?php declare(strict_types=1);

namespace Tetraquark\Abstract;
use \Tetraquark\{Exception as Exception, Block as Block, Log as Log, Validate as Validate};

abstract class VariableBlockAbstract extends BlockAbstract
{
    public function recreate(): string
    {
        $script = $this->getSubType() . ' ' . $this->getAlias($this->getName());

        if (\sizeof($this->getBlocks()) > 0 || \mb_strlen($this->getValue()) > 0) {
            $script .= '=';
        }

        foreach ($this->getBlocks() as $block) {
            $script .= rtrim(trim($block->recreate()), ';');
        }

        $value = $this->getValue();
        if (\mb_strlen($value) > 0) {
            $script .= $this->replaceVariablesWithAliases($value);
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
}
