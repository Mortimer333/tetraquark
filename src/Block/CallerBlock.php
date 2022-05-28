<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Content};
use \Tetraquark\Foundation\BlockAbstract as Block;

class CallerBlock extends Block implements Contract\Block
{
    public const ARROW_FUNCTION = 'arrow-function';
    protected array $endChars = [
        ';' => true,
    ];

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
        for ($i=$start + 1; $i < self::$content->getLength(); $i++) {
            $letter = self::$content->getLetter($i);

            // Skip whitespace
            if (Validate::isWhitespace($letter)) {
                continue;
            }

            // Skip String
            if (
                ($startsTemplate = Validate::isTemplateLiteralLandmark($letter, ''))
                || Validate::isStringLandmark($letter, '')
            ) {
                $i = $this->skipString($letter, $i + 1, self::$content, $startsTemplate);
                $letter = self::$content->getLetter($i);
            }

            if ($letter == '(') {
                $ignoreBrackets++;
                continue;
            }

            if ($ignoreBrackets > 0 && $letter == ')') {
                $ignoreBrackets--;
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
            if ((!$searchForArrow || $searchFroArgsEnd) && $letter == '=' && !Validate::isSpecial(self::$content->getLetter($i - 1))) {
                $skipDefaultDefinition = true;
                continue;
            }

            if ($searchFroArgsEnd && $letter == ')') {
                $end = $i;
                break;
            }

            // If we are searching for arrow and the first thing we find isn't equal sign it means this isn't arrow function
            if ($searchForArrow && $letter != '=') {
                break;
            } elseif ($searchForArrow && $letter == '=' && self::$content->getLetter($i + 1) ?? '' == ">") {
                // If this is arrow function then make this empty and skip current letter
                $this->setInstruction(new Content(''))
                    ->setInstructionStart($start)
                ;
                $arrow = new ArrowFunctionBlock($i + 1, '', $this);
                $this->setBlocks([
                    $arrow
                ]);
                $this->setSubtype(self::ARROW_FUNCTION);
                $this->setCaret($arrow->getCaret());
                return;
            }
        }

        if (is_null($end)) {
            $end = self::$content->getLength();
        }
        $this->setInstruction(new Content(''))
            ->setInstructionStart($start)
        ;
        $content = self::$content->subStr($start + 1, ($end - $start) - 1);
        if (strlen($content) > 0) {
            // $content = preg_replace('/[\n]/', ' ', $content);
            // $content = preg_replace('/[ \t]+/', ' ', $content) . ';';
            $this->blocks = $this->createSubBlocksWithContent($content . ';');
        }
        $this->setCaret($end);
    }

    public function recreate(): string
    {
        $script = '';
        if ($this->getSubtype() !== self::ARROW_FUNCTION) {
            $script .= '(';
        }

        foreach ($this->getBlocks() as $block) {
            $script .= rtrim($block->recreate(), ';');
        }

        $script = rtrim($script, ';');
        if ($this->getSubtype() !== self::ARROW_FUNCTION) {
            $script .= ')';
        }

        if (!$this->checkIfFirstLetterInNextSiblingIsADot()) {
            return $script . ';';
        }
        return $script;
    }
}
