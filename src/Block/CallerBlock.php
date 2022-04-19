<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\Log as Log;
use \Tetraquark\Contract as Contract;
use \Tetraquark\Block as Block;

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
        Log::log('-======');
        for ($i=$start + 1; $i < \mb_strlen(self::$content); $i++) {
            $letter = self::$content[$i];
            Log::log('Letter: ' . $letter);

            // Skip String
            if (
                ($startsTemplate = $this->isTemplateLiteralLandmark($letter, ''))
                || $this->isStringLandmark($letter, '')
            ) {
                $i = $this->skipString($i + 1, self::$content, $startsTemplate);
                $letter = self::$content[$i];
            }

            if ($letter == '(') {
                $ignorNextClose = true;
                continue;
            }

            if ($ignorNextClose && $letter == ')') {
                $ignorNextClose = false;
                continue;
            }

            // Skip whitespace
            if ($this->isWhitespace($letter)) {
                Log::log('Whitespace');
                continue;
            }

            // If we are currently skipping default then when letter is comma it means that we are at next argument and should stop
            if ($skipDefaultDefinition && $letter == ',') {
                Log::log('Default definintion end found');
                $skipDefaultDefinition = false;
                continue;
            }

            if ($skipDefaultDefinition) {
                Log::log('Skip letter and find end of default deinfinition');
                continue;
            }

            // If we have other function injected here then its a call and start search for its end
            if ($letter == '(') {
                Log::log('This is not arrow method, search for end of call');
                $searchFroArgsEnd = true;
                continue;
            }

            // If we have found ) before anything else then try to find arrow and determinate if this is arrow function
            if (!$searchFroArgsEnd && !$searchForArrow && $letter == ')') {
                Log::log('This might be arrow method, search for arrow');
                $searchForArrow = true;
                $end = $i + 1;
                $this->setCaret($i);
                continue;
            }

            // If we ahve found equal sign but we are not searching for arrow or we are searching for the end of arguments
            // then start search for default of this argument
            if ((!$searchForArrow || $searchFroArgsEnd) && $letter == '=') {
                Log::log('Start the skip of default definition');
                $skipDefaultDefinition = true;
                continue;
            }

            if ($searchFroArgsEnd && $letter == ')') {
                Log::log('Arguments end found');
                $end = $i;
                break;
            }

            // If we are searching for arrow and the first thing we find isn't equal sign it means this isn't arrow function
            if ($searchForArrow && $letter != '=') {
                Log::log('Start search for end of call 2');
                $end = $i;
                break;
            } elseif ($searchForArrow && $letter == '=' && self::$content[$i + 1] ?? '' == ">") {
                Log::log('Arrow function found');
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

        $this->setCaret($end);
        $this->setInstruction(trim(\mb_substr(self::$content, $start, $end - $start)))
            ->setInstructionStart($start)
        ;
        Log::log('Instruction starts at ' . $start . " and ends at " . $end . " => " . $this->getInstruction());
    }

    public function recreate(): string
    {
        $script = $this->removeAdditionalSpaces(
            $this->replaceVariablesWithAliases(
                $this->getInstruction()
            )
        );

        foreach ($this->getBlocks() as $block) {
            $script .= $block->recreate();
        }

        return rtrim($script, ';') . ';';
    }
}
