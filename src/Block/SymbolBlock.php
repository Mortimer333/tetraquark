<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate};
use \Tetraquark\Abstract\VariableBlockAbstract as VariableBlock;

class SymbolBlock extends VariableBlock implements Contract\Block
{
    public function objectify(int $start = 0)
    {
        Log::log('Start: ' . $start);
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
        Log::log('Start search for end: ' . self::$content[$start + 1]);
        for ($i=$start + 1; $i < \mb_strlen(self::$content); $i++) {
            $letter = self::$content[$i];
            Log::log('letter: ' . $letter);
            if ($letter == ';' || $letter == ',' || $letter == ']' || $letter == ')' || $letter == '}') {
                Log::log('is semi, end hrer: ' . $i);
                $end = $i - 1;
                break;
            }

            if (!$SFWhitespace && Validate::isWhitespace($letter)) {
                Log::log('not searching for white space, but whitespace it is, skip: ' . $i);
                continue;
            }

            if (!$SFWhitespace) {
                Log::log('Start search for whitespace: ' . $i);
                $SFWhitespace = true;
            }

            if (
                ($startsTemplate = Validate::isTemplateLiteralLandmark($letter, ''))
                || Validate::isStringLandmark($letter, '')
            ) {
                Log::log('Is string skip: ' . $i);
                $i = $this->skipString($i + 1, self::$content, $startsTemplate);
                $letter = self::$content[$i];
                Log::log('New letter: ' . $letter . ', ' . $i);
            }

            if ($SFWhitespace && Validate::isWhitespace($letter)) {
                Log::log('End found: ' . $letter . ', ' . $i);
                $end = $i;
                break;
            }
        }

        Log::log('=================');
        if (\is_null($end)) {
            Log::log('End is null!');
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
