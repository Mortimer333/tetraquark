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
        // Check if its not arrow method
        for ($i=$start; $i < \mb_strlen(self::$content); $i++) {
            $letter = self::$content[$i];
            if ($this->isWhitespace($letter)) {
                continue;
            }

            if ($letter == ')') {
                $searchForArrow = true;
                $end = $i;
                $this->setCaret($i - 1);
                continue;
            }

            if ($searchForArrow && $letter != '=') {
                break;
            } elseif ($searchForArrow && $letter == '=' && self::$content[$i + 1] == ">") {
                // If this is arrow function then make this empty and skip current letter
                $this->setInstruction('')
                    ->setInstructionStart($start)
                ;
                $this->setCaret($start);
                return;
            }
        }

        // Search for start
        $SFWhitespace = false;
        for ($i=$start - 1; $i >= 0; $i--) {
            $letter = self::$content[$i];
            if (!$SFWhitespace && $this->isWhitespace($letter)) {
                continue;
            }

            if (!$this->isWhitespace($letter)) {
                $SFWhitespace = true;
            }

            if ($SFWhitespace && $this->isWhitespace($letter) || $this->isSpecial($letter)) {
                $start = $i + 1;
                break;
            }
        }

        $this->setInstruction(trim(\mb_substr(self::$content, $start, $end - $start)))
            ->setInstructionStart($start)
        ;
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

        return $script;
    }
}
