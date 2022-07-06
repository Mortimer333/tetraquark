<?php declare(strict_types=1);

namespace Tetraquark;

/**
 *  Class for reading script and seperating it into managable blocks
 */
class Reader
{
    public function __construct(protected array $schema)
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

        $content = $this->removeCommentsAndAdditional(new Content(trim($script)));
        $maps    = $this->generateBlocksMap();
        echo $content->__toString();
        echo json_encode($maps);
    }

    public function removeCommentsAndAdditional(Content $content): Content
    {
        if (!isset($this->schema['comments']) || sizeof($this->schema['comments']) == 0) {
            return $content;
        }

        $comment = [
            "schema" => $this->schema['comments'] ?? [],
            "start"  => null,
            "map"    => null
        ];

        $additional = $this->schema['remove']['additional'] ?? null;

        for ($i=0; $i < $content->getLength(); $i++) {
            $letter     = $content->getLetter($i);
            $nextLetter = $content->getLetter($i + 1);

            // skipping strings
            $i          = Str::skip($letter, $i, $content);
            $letter     = $content->getLetter($i);
            $nextLetter = $content->getLetter($i + 1);
            if (is_null($letter)) {
                break;
            }

            $comment["map"] = is_null($comment["map"]) ? ($comment['schema'][$letter] ?? null) : $comment["map"][$letter] ?? null;

            if (is_string($comment["map"])) {
                $i = $this->removeComment($comment["map"], $content, $comment["start"], $i);
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

            is_callable($additional) && $additional($i, $content, $letter, $nextLetter, $this->schema);
        }

        return $content;
    }

    public function removeComment(string $commentEnd, Content &$content, int $start, int $currentPos): int
    {
        $end = $this->findClosestMatch($commentEnd, $content, $currentPos + 1);
        // If returned false then the rest of the script is commented, just remove it
        if ($end === false) {
            $content->remove($start, null);
        } else {
            $content->remove($start, $end + 1 - $start);
        }

        // moving back by two is make sure that we didn't miss anything for additional checks
        return $start > 0 ? $start - 2 : -1;
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

    public function generateBlocksMap(): array
    {
        $namespace    = $this->schema['namespace'   ] ?? throw new Exception('Namespace not found'   , 404);
        $instructions = $this->schema['instructions'] ?? throw new Exception('Instructions not found', 404);

        $maps = [];
        foreach ($instructions as $instr => $blockName) {
            $maps[] =  $this->sliceIntoSteps($this->translateInstructionToMap($instr), $blockName);
        }
        $map = $this->mergeMaps($maps);

        return $map;
    }

    public function mergeMaps(array $maps): array
    {
        $merged = [];
        foreach ($maps as $map) {
            $firstKey = array_key_first($map);
            if (isset($merged[$firstKey])) {
                $merged[$firstKey] = $this->mergeMaps([$merged[$firstKey], $map[$firstKey]]);
            } else {
                $merged[$firstKey] = $map[$firstKey];
            }
        }
        return $merged;
    }

    public function createWell(string|array $rope, mixed $end = [], int $counter = 0): mixed
    {
        // Did we hit bottom?
        if (!isset($rope[$counter])) {
            return $end;
        }
        return [$rope[$counter] => $this->createWell($rope, $end, $counter + 1)];
    }

    public function sliceIntoSteps(array $map, string $blockName, int $stepCounter = 0): array|string
    {
        $types = [
            "landmark" => function (array $step) use ($map, $blockName, $stepCounter) {
                $res = $this->createWell($step['item'], $this->sliceIntoSteps($map, $blockName, $stepCounter + 1));
                return $res;
            },
            "method" => function (array $step) use ($map, $blockName, $stepCounter) {
                $steps = [];
                $nextStep = $this->sliceIntoSteps($map, $blockName, $stepCounter + 1);
                foreach ($step['item'] as $item) {
                    $steps['*' . $item['name']] = $nextStep;
                }
                return $steps;
            },
            "default" => function () use ($blockName) {
                return $blockName;
            }
        ];

        return ($types[$map[$stepCounter]['type'] ?? null] ?? $types['default'])($map[$stepCounter] ?? null);
    }

    public function translateInstructionToMap(string $instr): array
    {
        $instr = new Content($instr);
        $map = $this->seperateMethodsAndLandmarks($instr);

        return $map;
    }

    public function seperateMethodsAndLandmarks(Content $instr): array
    {
        $map = [];
        $currentItem = '';
        $methods = [];
        $lastMethodLandmark = '|';
        $inMethod = false;

        for ($i=0; $i < $instr->getLength(); $i++) {
            $letter = $instr->getLetter($i);

            // skip this letter and just add next one
            if ($letter === "\\") {
                if ($inMethod) {
                    if (strlen($currentItem) > 0) {
                        $methods[] = $this->createMethodMapItem($letter, $lastMethodLandmark, $currentItem);
                    }
                    $lastMethodLandmark = '|';
                    $map[] = $this->createMapItem($methods, "method");
                    $methods = [];
                    $currentItem = '';
                    $inMethod = false;
                    continue;
                }
                $currentItem .= $instr->getLetter($i + 1);
                $i++;
                continue;
            }

            if ($letter === "/") {
                $map[] = $this->createMapItem($currentItem, "landmark");
                $currentItem = '';
                $inMethod = true;
                continue;
            }

            if ($inMethod && ($letter === '|')) {
                $methods[] = $this->createMethodMapItem($letter, $lastMethodLandmark, $currentItem);
                $currentItem = '';
                continue;
            }

            $currentItem .= $letter;
        }

        if (strlen($currentItem) > 0) {
            $map[] = $this->createMapItem($currentItem, "landmark");
        }

        return $map;
    }

    private function createMapItem(string|array $item, string $type): array
    {
        return ["item" => $item, "type" => $type];
    }

    private function createMethodMapItem(string $letter, string &$lastMethodLandmark, string $currentItem): array
    {
        $types = [
            '|' => "default",
            '>' => 'save_output',
        ];

        $type = $types[$lastMethodLandmark] ?? throw new Exception("Method type unknown: " . htmlentities($letter), 400);
        $lastMethodLandmark = $letter;
        return [
            "name" => $currentItem,
            "type" => $type
        ];
    }
}
