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
            "start" => null,
            "map" => null
        ];

        $additional = $this->schemat['remove']['additional'] ?? null;

        for ($i=0; $i < $content->getLength(); $i++) {
            $letter     = $content->getLetter($i);
            $nextLetter = $content->getLetter($i + 1);

            if (is_null($nextLetter)) {
                break;
            }

            $comment["map"] = is_null($comment["map"]) ? ($comment['schema'][$letter] ?? null) : $comment["map"][$letter] ?? null;

            if (is_string($comment["map"])) {
                $end = $this->findClosestMatch($comment["map"], $content, $i + 1);
                // If returned false then the rest of the script is commented, just remove it
                if ($end === false) {
                    $content->remove($comment["start"], null);
                    return;
                }
                $content->iremove($comment["start"], $end);
                $i = $end;
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
                if (is_null($letter) || is_null($nextLetter)) {
                    break;
                }
            }

            is_callable($additional) && $additional($i, $content, $letter, $nextLetter, $this->schemat);
        }
    }

    public function findClosestMatch(string $needle, Content $content, int $start = 0): bool | int
    {
        $needleSize = \mb_strlen($needle);
        for ($i=0; $i < $content->getLength(); $i++) {
            $match = $content->subStr($i, $needleSize);
            if ($needle == $match) {
                return $i + $needleSize;
            }
        }
        return false;
    }
}
