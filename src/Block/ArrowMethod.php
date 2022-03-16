<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\Contract as Contract;
use \Tetraquark\Block as Block;

class ArrowMethod extends Block implements Contract\Block
{
    public function objectify(int $start = 0)
    {
        $searchForBracketsStart = false;
        $searchForNextWhiteSpace = false;
        $subStart = null;
        $subEnd   = null;
        for ($i=$start; $i >= 0; $i--) {
            $letter = $this->content[$i];
            echo json_encode($letter) . $i . PHP_EOL;
            if ($letter == ')') {
                $searchForBracketsStart = true;
                continue;
            }

            if ($searchForBracketsStart && $letter == '(') {
                echo "Start found " . $letter . $i . PHP_EOL;
                $subStart = $i;
                $this->subtype = 'parenthesis';
                break;
            }

            if (preg_match('/[^\s]/', $letter)) {
                $searchForNextWhiteSpace = true;
                continue;
            }

            if ($searchForNextWhiteSpace && preg_match('/[^\s]/', $letter)) {
                echo "Start found " . $letter . $i . PHP_EOL;
                $subStart = $i;
                $this->subtype = 'no-parenthesis';
                break;
            }
        }

        for ($i=$start + 1; $i < strlen($this->content); $i++) {
            $letter = $this->content[$i];

            if ($letter == '{') {
                echo "End found " . $letter . $i . PHP_EOL;
                $this->subtype .= ':brackets';
                $subEnd = $i + 1;
                break;
            }

            if (preg_match('/[^\s]/', $letter)) {
                echo "End found " . $letter . $i . PHP_EOL;
                $this->subtype .= ':no-brackets';
                $subEnd = $i;
                break;
            }
        }

        $this->instruction = substr($this->content, $subStart, $subEnd - $subStart);
        $this->content = substr($this->content, $subEnd + 1);
    }

    public function multiLine(): bool
    {
        return isset($this->instruction) && $this->instruction[strlen($this->instruction) - 1] == '{';
    }
}
