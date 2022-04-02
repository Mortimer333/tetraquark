<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\Log as Log;
use \Tetraquark\Exception as Exception;
use \Tetraquark\Contract as Contract;
use \Tetraquark\MethodBlock as MethodBlock;

class ArrowFunctionBlock extends MethodBlock implements Contract\Block
{
    protected string $value = '';
    /*
        Possible function syntaxes:
        - () => {}
        - x => {}
        - x => x + 1
        - (x) => x + 1
        We can check if its a function if there is `=>`
    */
    public function objectify(int $start = 0)
    {
        Log::log("New Arrow method", 1);
        $subStart = $this->findStart($start);
        if (\is_null($subStart)) {
            throw new Exception('Start of arrow method not found', 404);
        }

        $subEnd = $this->findEnd($start);
        if (\is_null($subEnd)) {
            throw new Exception('End of arrow method not found', 404);
        }

        if ($this->isMultiLine()) {
            $instruction = str_replace("\n", ' ', substr(self::$content, $subStart, $subEnd - $subStart));
        } else {
            $instruction = str_replace("\n", ' ', substr(self::$content, $subStart, ($start + 1) - $subStart));
            $this->setValue(trim(str_replace("\n", ' ', substr(self::$content, $start + 1, $subEnd - $start))));
        }

        $this->setInstruction($instruction)
            ->setInstructionStart($subStart)
        ;
        if ($this->isMultiLine()) {
            $this->endFunction = true;
            $this->endChars = [
                '}' => true
            ];
            $this->createSubBlocks();
        }

        $this->findAndSetArguments();

        $this->setName('');
    }

    public function setValue(string $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    protected function findAndSetArguments(): void
    {
        $instr = $this->getInstruction();
        $SFParenthesis = false;
        $SFWhitespace = false;
        $word = '';
        Log::increaseIndent();
        for ($i=0; $i < \strlen($instr); $i++) {
            $letter = $instr[$i];
            Log::log("Letter: "  . $letter . ", Word: " . $word, 1);
            if (!$SFParenthesis && $letter == '(') {
                Log::log("Start parenthesis search", 1);
                $SFParenthesis = true;
                $word = '';
                continue;
            }

            if ($this->isWhitespace($letter)) {
                Log::log("Whitespace!", 1);
                if ($SFWhitespace) {
                    Log::log("Found next whitespace, add arg and end iteration", 1);
                    $this->addArgument($word);
                    break;
                }
                continue;
            } elseif (!$SFParenthesis) {
                Log::log("function has only one arg, start search for it...", 1);
                $word .= $letter;
                $SFWhitespace = true;
                continue;
            }

            if ($SFParenthesis && $letter == ',') {
                Log::log("Arg seperator found, add arg", 1);
                $this->addArgument($word);
                $word = '';
                continue;
            }


            if ($SFParenthesis && $letter == ')') {
                Log::log("Stop search for parenthesis", 1);
                $this->addArgument($word);
                break;
            }

            $word .= $letter;
        }
        Log::decreaseIndent();
    }

    public function isMultiLine(): bool
    {
        return $this->subtype == 'parenthesis:brackets' || $this->subtype == 'no-parenthesis:brackets';
    }

    protected function findEnd(int $start):? int
    {
        Log::log("Start search for end", 1);
        Log::increaseIndent();
        $searchForEnd = false;
        $subEnd = null;
        for ($i=$start + 1; $i < strlen(self::$content); $i++) {
            $letter = self::$content[$i];
            Log::log("Letter " . $letter, 2);

            if ($searchForEnd && $this->isEndChar($letter)) {
                Log::log("End char found, setting the end...", 2);
                $subEnd = $i - 1;
                $this->setCaret($i - 1);
                break;
            }

            if (!$searchForEnd && $letter == '{') {
                Log::log("Letter is bracket! Multi line function.", 2);
                $this->subtype .= ':brackets';
                $subEnd = $i + 1;
                $this->setCaret($i + 1);
                break;
            }

            if (!$searchForEnd && !$this->isWhitespace($letter)) {
                Log::log("Found non white space and not bracket. One line arrow method", 2);
                $this->subtype .= ':no-brackets';
                $searchForEnd = true;
                continue;
            }
        }
        Log::decreaseIndent();
        return $subEnd;
    }

    protected function findStart(int $start):? int
    {
        $searchForBracketsStart  = false;
        $searchForNextWhiteSpace = false;
        $subStart = null;
        Log::increaseIndent();
        for ($i=$start - 2; $i >= 0; $i--) {
            $letter = self::$content[$i];
            Log::log("New letter `" . $letter . "`", 2);
            if (!$searchForBracketsStart && $letter == ')') {
                Log::log("Start search for brackets", 2);
                $searchForBracketsStart = true;
                continue;
            }

            if ($searchForBracketsStart && $letter == '(') {
                Log::log("Bracket found", 2);
                $subStart = $i;
                $this->subtype = 'parenthesis';
                break;
            }

            if (!$searchForNextWhiteSpace && !$this->isWhitespace($letter)) {
                Log::log("Start search for whitspace, letter found before bracket", 2);
                $searchForNextWhiteSpace = true;
                continue;
            }

            if ($searchForNextWhiteSpace && $this->isWhitespace($letter)) {
                Log::log("White space found", 2);
                $subStart = $i + 1;
                $this->subtype = 'no-parenthesis';
                break;
            }
        }
        Log::decreaseIndent();
        return $subStart;
    }

    public function recreate(): string
    {
        $script = '(' . $this->getAliasedArguments() . ')=>';
        if (!$this->isMultiLine()) {
            $script .= $this->removeAdditionalSpaces(
                $this->replaceVariablesWithAliases($this->getValue()) . ';'
            );
        } else {
            $script .= '{';
            foreach ($this->getBlocks() as $block) {
                $script .= $block->recreate();
            }
            $script .= '};';
        }
        return $script;
    }
}
