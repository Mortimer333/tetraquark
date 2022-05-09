<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log as Log, Exception as Exception, Contract as Contract, Validate as Validate};
use \Tetraquark\Foundation\BlockAbstract as Block;

class CallerBlock extends Block implements Contract\Block
{
    public function objectify(int $start = 0)
    {
        $this->setName('');
        $searchForArrow = false;
        $end = null;
        $skipDefaultDefinition = false;
        $searchFroArgsEnd = false;
        $ignorNextClose = false;
        // Check if its not arrow method
        $ignoreBrackets = 0;
        for ($i=$start + 1; $i < \mb_strlen(self::$content); $i++) {
            $letter = self::$content[$i];

            // Skip String
            if (
                ($startsTemplate = Validate::isTemplateLiteralLandmark($letter, ''))
                || Validate::isStringLandmark($letter, '')
            ) {
                $i = $this->skipString($i + 1, self::$content, $startsTemplate);
                $letter = self::$content[$i];
            }

            if ($letter == '(') {
                $ignoreBrackets++;
                continue;
            }

            if ($ignoreBrackets > 0 && $letter == ')') {
                $ignoreBrackets--;
                continue;
            }

            // Skip whitespace
            if (Validate::isWhitespace($letter)) {
                continue;
            }

            // If we are currently skipping default then when letter is comma it means that we are at next argument and should stop
            if ($skipDefaultDefinition && $letter == ',') {
                $skipDefaultDefinition = false;
                continue;
            }

            if ($skipDefaultDefinition) {
                continue;
            }

            // If we have other function injected here then its a call and start search for its end
            if ($letter == '(') {
                $searchFroArgsEnd = true;
                continue;
            }

            // If we have found ) before anything else then try to find arrow and determinate if this is arrow function
            if (!$searchFroArgsEnd && !$searchForArrow && $letter == ')') {
                $searchForArrow = true;
                $end = $i;
                continue;
            }

            // If we ahve found equal sign but we are not searching for arrow or we are searching for the end of arguments
            // then start search for default of this argument
            if ((!$searchForArrow || $searchFroArgsEnd) && $letter == '=') {
                $skipDefaultDefinition = true;
                continue;
            }

            if ($searchFroArgsEnd && $letter == ')') {
                $end = $i;
                break;
            }

            // If we are searching for arrow and the first thing we find isn't equal sign it means this isn't arrow function
            if ($searchForArrow && $letter != '=') {
                // $end = $i;
                break;
            } elseif ($searchForArrow && $letter == '=' && self::$content[$i + 1] ?? '' == ">") {
                // If this is arrow function then make this empty and skip current letter
                $this->setInstruction('')
                    ->setInstructionStart($start)
                ;
                $arrow = new ArrowFunctionBlock($i + 1);
                $this->setBlocks([
                    $arrow
                ]);
                $this->setCaret($arrow->getCaret());
                return;
            }
        }

        if (is_null($end)) {
            $end = \mb_strlen(self::$content);
        }
        Log::log('End: ' . $end . ', Start: ' . $start . ", end letter: " . self::$content[$end] . ', start letter: ' . self::$content[$start]);
        $this->setCaret($end);
        $this->setInstruction(\mb_substr(self::$content, $start, ($end - $start) + 1))
            ->setInstructionStart($start)
        ;
    }

    public function recreate(): string
    {
        $script = $this->replaceVariablesWithAliases(
            $this->getInstruction()
        );

        foreach ($this->getBlocks() as $block) {
            $script .= $block->recreate();
        }

        $script = rtrim($script, ';');

        if (!$this->checkIfFirstLetterInNextSiblingIsADot()) {
            return $script . ';';
        }
        return $script;
    }
}
