<?php declare(strict_types=1);

namespace Tetraquark;

/**
 *  Class for reading script and seperating it into managable blocks
 */
class Reader
{
    public function __construct(protected array $schemat)
    {
    }

    public function read(string $script, bool $isPath = false)
    {
        if ($isPath) {
            if (!is_file($script)) {
                throw new Exception("Passed file was not found", 404);
            }

            $script = file_get_contents($script);
        }

        $content = new Content(trim($script));
        $this->removeAdditionalAndComments($content);
        echo $content->__toString();
    }

    public function removeAdditionalAndComments(Content &$content)
    {
        $comment = [
            "schema" => $this->schemat['comments'] ?? [],
            "start"  => null,
            "map"    => null
        ];

        $additional = $this->schemat['remove']['additional'] ?? null;

        for ($i=0; $i < $content->getLength(); $i++) {
            $letter     = $content->getLetter($i);
            $nextLetter = $content->getLetter($i + 1);

            $comment["map"] = is_null($comment["map"]) ? ($comment['schema'][$letter] ?? null) : $comment["map"][$letter] ?? null;

            if (is_string($comment["map"])) {
                $end = $this->findClosestMatch($comment["map"], $content, $i + 1);
                // If returned false then the rest of the script is commented, just remove it
                if ($end === false) {
                    $content->remove($comment["start"], null);
                    return;
                }

                $content->remove($comment["start"], $end + 1 - $comment["start"]); /** @log Start */
                // moving back by two is make sure that we didn't miss anything for additional checks
                $i = $comment["start"] > 0 ? $comment["start"] - 2 : -1;
                $comment["start"] = null;
                $comment["map"] = null;
                continue;
            }

            if (is_array($comment["map"]) && is_null($comment["start"])) {
                $comment["start"] = $i;
                continue;
            } elseif (is_null($comment["map"]) && !is_null($comment["start"])) {
                $comment["start"] = null;
            }

            if (
                ($startsTemplate = Validate::isTemplateLiteralLandmark($letter, ''))
                || Validate::isStringLandmark($letter, '')
            ) {
                $i = Str::skip($letter, $i + 1, $content, $startsTemplate);
                $letter     = $content->getLetter($i);
                $nextLetter = $content->getLetter($i + 1);
                if (is_null($letter)) {
                    break;
                }
            }

            is_callable($additional) && $additional($i, $content, $letter, $nextLetter, $this->schemat);
        }
    }

    public function findClosestMatch(string $needle, Content $content, int $start = 0): bool | int
    {
        $needleSize = \mb_strlen($needle);
        $needleFirst = $needle[0] ?? throw new Exception("Needle can't be empty", 400);
        for ($i=$start; $i < $content->getLength(); $i++) {
            if ($needleFirst != $content->getLetter($i)) {
                continue;
            }

            $match = $content->subStr($i, $needleSize);
            if ($needle == $match) {
                return $i + ($needleSize - 1);
            }
        }
        return false;
    }
}
