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
        $this->setInstructionStart($start);
        $this->setName('');
        $end = $start;
        $string = false;
        $template = false;
        $stringEnd = false;
        $getSubBlocks = false;
        $checkFirsLetter = true;
        $start += 1;
        Log::setMaxVerboseLevel(3);
        Log::log("============", 1);
        for ($i=$start; $i < \mb_strlen(self::$content); $i++) {
            $letter = self::$content[$i];
            $end = $i;
            Log::log("Letter `" . $letter . "`, i: " . $i, 3);
            if ($this->isWhitespace($letter)) {
                continue;
            }

            // If first letter is not string landmark then start gathering sub blocks
            if ($checkFirsLetter) {
                Log::log("Check first letter", 3);
                if ($letter != '"' && $letter != "'" && $letter != '`') {
                    Log::log("is not string!", 3);
                    $getSubBlocks = true;
                    $end = $i;
                    break;
                } else {
                    Log::log("is string!", 3);
                    $checkFirsLetter = false;
                }
            }

            if ($template && $this->startsTemplateLiteralVariable($letter, self::$content, $i)) {
                $getSubBlocks = true;
                $end = $start;
                $string = false;
                $template = false;
                break;
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
                Log::log("Check string!", 3);
                if ($letter == "'" || $letter == '"') {
                    Log::log("is nromal stirng!", 3);
                    $string = true;
                    continue;
                }

                if ($letter == '`') {
                    Log::log("is template!", 3);
                    $template = true;
                    continue;
                }
            } elseif (!$stringEnd && $string && $this->isStringLandmark($letter, self::$content[$i - 1], true)) {
                Log::log("string end!", 3);
                $stringEnd = true;
                continue;
            } elseif (!$stringEnd && $template && $this->isTemplateLiteralLandmark($letter, self::$content[$i - 1], true)) {
                Log::log("string template end!", 3);
                $stringEnd = true;
                continue;
            }

            if ($letter == ']' || $stringEnd) {
                Log::log("string end found or and of array!", 2);
                $end = $i;
                if ($stringEnd && $letter != ']') {
                    $string = false;
                    $template = false;
                    Log::log("strign end but there is another letter!", 1);
                    $getSubBlocks = true;
                    $end = $start;
                }
                break;
            }
        }
        Log::setMaxVerboseLevel(0);
        $this->setCaret($end);
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
            $script .= "'" . $this->replaceVariablesWithAliases($name) . "'";
        } else {
            foreach ($this->getBlocks() as $block) {
                $script .= rtrim($block->recreate(), ';');
            }
        }


        return $script . ']';
    }
}
