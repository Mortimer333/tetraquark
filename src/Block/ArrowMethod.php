<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Xeno\X as Xeno;
use \Tetraquark\Log as Log;
use \Tetraquark\Exception as Exception;
use \Tetraquark\Contract as Contract;
use \Tetraquark\Block as Block;

class ArrowMethod extends Block implements Contract\Block
{
    /*
        Possible function syntaxes:
        - () => {}
        - x => {}
        - x => x + 1
        We can check if its a function if there is `)` or `=>` before {
    */
    public function objectify(int $start = 0)
    {
        Log::log("=== New Arrow method", 1);
        $searchForBracketsStart  = false;
        $searchForNextWhiteSpace = false;
        $subStart = null;
        $subEnd   = null;
        Log::increaseIndent();
        for ($i=$start - 2; $i >= 0; $i--) {
            $letter = $this->content[$i];
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

        if (\is_null($subStart)) {
            throw new Exception('Start of arrow method not found', 404);
        }

        Log::log("Start search for end", 1);
        Log::increaseIndent();
        $searchForEnd = false;
        for ($i=$start + 1; $i < strlen($this->content); $i++) {
            $letter = $this->content[$i];
            Log::log("Letter " . $letter, 2);

            if ($searchForEnd && $this->isEndChar($letter)) {
                Log::log("End char found, setting the end...", 2);
                $subEnd = $i + 1;
                $this->setCaret($i + 1);
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

        $instruction = (new Xeno($this->content))->substr($subStart, $subEnd - $subStart)->replace("\n", ' ');
        $this->setInstruction($instruction);
        if ($this->isMultiLine()) {
            $this->endFunction = true;
        }
    }

    public function isMultiLine(): bool
    {
        return $this->subtype == 'parenthesis:brackets' || $this->subtype == 'no-parenthesis:brackets';
    }
}
