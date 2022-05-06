<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate};
use \Tetraquark\Foundation\VariableBlockAbstract as VariableBlock;

class SymbolBlock extends VariableBlock implements Contract\Block
{
    public function objectify(int $start = 0)
    {
        $this->setName('');
        // Find symbol start
        $properStart = null;
        for ($i=$start - 1; $i >= 0; $i--) {
            $letter = self::$content[$i];
            if (!Validate::isSymbol($letter)) {
                $properStart = $i + 1;
                break;
            }
        }
        if (\is_null($properStart)) {
            $properStart = 0;
        }

        $SFWhitespace = false;
        $end = null;
        for ($i=$start + 1; $i < \mb_strlen(self::$content); $i++) {
            $letter = self::$content[$i];
            if ($letter == ';' || $letter == ',' || $letter == ']' || $letter == ')' || $letter == '}') {
                $end = $i - 1;
                break;
            }

            if (!$SFWhitespace && Validate::isWhitespace($letter)) {
                continue;
            }

            if (!$SFWhitespace) {
                $SFWhitespace = true;
            }

            if (
                ($startsTemplate = Validate::isTemplateLiteralLandmark($letter, ''))
                || Validate::isStringLandmark($letter, '')
            ) {
                $i = $this->skipString($i + 1, self::$content, $startsTemplate);
                $letter = self::$content[$i];
            }

            if ($SFWhitespace && Validate::isWhitespace($letter)) {
                $end = $i;
                break;
            }
        }

        if (\is_null($end)) {
            $end = \mb_strlen(self::$content) - 1;
        }

        $this->setInstruction(\mb_substr(self::$content, $properStart, $end - $properStart + 1))
            ->setInstructionStart($properStart)
            ->setCaret($end);
    }

    public function recreate(): string
    {
        return $this->replaceVariablesWithAliases($this->getInstruction());
    }
}
