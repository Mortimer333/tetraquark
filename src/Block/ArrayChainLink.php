<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\Log as Log;
use \Tetraquark\Contract as Contract;
use \Tetraquark\Block as Block;

class ArrayChainLink extends Block implements Contract\Block
{
    protected array $endChars = [
        ']' => true
    ];

    public function objectify(int $start = 0)
    {
        $this->setInstruction('');
        $this->setName('');
        $end = $start;
        $string = false;
        $template = false;
        $stringEnd = false;
        $getSubBlocks = false;
        $checkFirsLetter = true;
        $start += 1;
        for ($i=$start; $i < \mb_strlen(self::$content); $i++) {
            $letter = self::$content[$i];
            $end = $i;
            Log::log("Letter `" . $letter . "`, i: " . $i);
            if ($this->isWhitespace($letter)) {
                Log::log("Is whitespace ");
                continue;
            }

            // If first letter is not string landmark then start gathering sub blocks
            if ($checkFirsLetter) {
                Log::log("Check first letter");
                if ($letter != '"' && $letter != "'" && $letter != '`') {
                    Log::log("is not string!");
                    $getSubBlocks = true;
                    $end = $i - 1;
                    break;
                } else {
                    Log::log("is string!");
                    $checkFirsLetter = false;
                }
            }

            // Possible syntaxes:
            // - ['property'] - replace `property` with alias
            // - ['proper' + int] - replace only int
            // - [`proper${int}`] - again replace int
            // - [`property`] - replace `property` with alias
            // - ["property"] - replace `property` with alias
            // - ["propert" + func(str)] - replace func and str
            // - [func(str) + "propert"] - replace func and str
            // Solution: check if string is the only part of this call. If not start getting subblocks after it ended


            if (!$string && !$template && !$stringEnd) {
                Log::log("Check string!");
                if ($letter == "'" || $letter == '"') {
                    Log::log("is nromal stirng!");
                    $string = true;
                    continue;
                }

                if ($letter == '`') {
                    Log::log("is template!");
                    $template = true;
                    continue;
                }
            } elseif (!$stringEnd && $string && $this->isStringLandmark($letter, self::$content[$i - 1], true)) {
                Log::log("string end!");
                $stringEnd = true;
                continue;
            } elseif (!$stringEnd && $template && $this->isTemplateLiteralLandmark($letter, self::$content[$i - 1], true)) {
                Log::log("string template end!");
                $stringEnd = true;
                continue;
            }

            if ($letter == ']' || $stringEnd) {
                Log::log("string end found or and of array!");
                if ($stringEnd && $letter != ']') {
                    Log::log("strign end but there is another letter!");
                    $getSubBlocks = true;
                }
                $end = $i;
                break;
            }
        }
        $this->setCaret($end);
        echo $start . ", " .  ($end - $start);
        $name = trim(\mb_substr(self::$content, $start, $end - $start));
        if ($string) {
            $name = \mb_substr($name, 1, -1);
            $this->setName($name);
        } elseif ($template) {
            if (strpos($name, '${') === false) {
                $name = \mb_substr($name, 1, -1);
                $this->setName($name);
            }
        }

        if ($getSubBlocks) {
            $this->createSubBlocks();
        }
    }

    public function recreate(): string
    {
        $name = $this->getName();
        $script = '[';
        if (\mb_strlen($name)) {
            Log::log('Replace ' . $name);
            $script .= "'" . $this->replaceVariablesWithAliases($name) . "'";
        } else {
            foreach ($this->getBlocks() as $block) {
                $script .= $block->recreate();
            }
        }


        return $script . ']';
    }
}
