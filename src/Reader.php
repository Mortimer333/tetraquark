<?php declare(strict_types=1);

namespace Tetraquark;

/**
 *  Class for reading script and seperating it into managable blocks
 */
class Reader
{
    protected array $methods = [];
    protected array $script = [];

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

        $script  = $this->customPrepate($script);
        $content = $this->removeCommentsAndAdditional(new Content($script));
        $map     = $this->generateBlocksMap();
        echo json_encode($this->methods, JSON_PRETTY_PRINT);
        list($this->script, $end) = $this->objectify($content, $map);
        echo $content->__toString();
        echo json_encode($map, JSON_PRETTY_PRINT);
        echo json_encode($this->script, JSON_PRETTY_PRINT);
    }

    public function objectify(Content $content, array $map, int $start = 0): array
    {
        $landmark = $map;
        $data     = [];
        $lmStart  = null;
        $script   = [];
        $settings = [
            "skip" => 0
        ];

        try {
            for ($i=$start; $i < $content->getLength(); $i++) {
                try {
                    $i      = Str::skip($content->getLetter($i), $i, $content);
                    $letter = $content->getLetter($i);

                    if (isset($landmark[$letter])) {
                        $this->resolveStringLandmark($letter, $landmark, $lmStart, $script, $i, $content, $data, $settings, $map);
                        continue;
                    }

                    if (isset($landmark['_m']) && $this->resolveMethodLandmark($letter, $landmark, $lmStart, $script, $i, $content, $data, $settings, $map)) {
                        continue;
                    }

                    $this->clearObjectify($landmark, $map, $data, $lmStart);
                } catch (\Exception $e) {
                    if ($e->getMessage() !== 'skip') {
                        throw $e;
                    }
                }
            }
        } catch (\Exception $e) {
            if ($e->getMessage() !== 'finish') {
                throw $e;
            }
        }


        return [$script, $i];
    }

    private function clearObjectify(array &$landmark, array $map, array &$data, ?int &$lmStart)
    {
        $landmark = $map;
        $data     = [];
        $lmStart  = null;
    }

    public function resolveStringLandmark(
        string $letter, array &$landmark, ?int &$lmStart, array &$script,
        int &$i, Content &$content, array &$data, array &$settings, array $map
    ): void {
        $landmark = $landmark[$letter];
        if (is_null($lmStart)) {
            $lmStart = $i;
        }
        if (isset($landmark['_stop'])) {
            $this->resolveSettings($settings, $landmark);
            $script[] = $this->saveLandmark($landmark, $lmStart, $i, $content, $data);
            $this->clearObjectify($landmark, $map, $data, $lmStart);
        }
    }

    public function resolveMethodLandmark(
        string &$letter, array &$landmark, ?int &$lmStart, array &$script,
        int &$i, Content &$content, array &$data, array &$settings, array $map
    ): bool {
        foreach ($landmark['_m'] as $methodName => $step) {
            $method = $this->methods[$methodName]
                ?? throw new Exception("Method " . htmlentities($methodName) . " not found", 404);

            $callable = $this->schema['methods'][$method['name']]
                ?? throw new Exception("Method " . htmlentities($methodName) . " not defined", 400);

            is_callable($callable)
                or throw new Exception("Method " . htmlentities($methodName) . " is not callable", 400);

            $res = $callable($content, $letter, $i, $data, ...$method['params']);

            if (!$res) {
                continue;
            }

            $landmark = $step;
            if (is_null($lmStart)) {
                $lmStart = $i;
            }
            if (isset($landmark['_stop'])) {
                $this->resolveSettings($settings, $landmark);
                $script[] = $this->saveLandmark($landmark, $lmStart, $i, $content, $data);
                $this->clearObjectify($landmark, $map, $data, $lmStart);
            }
            return true;
        }
        return false;
    }

    public function resolveSettings(array &$settings, array $landmark): void
    {
        if (isset($landmark['_finish'])) {
            throw new \Exception('finish');
        }

        if ($settings['skip'] > 0) {
            $settings['skip']--;
            throw new Exception('skip');
        }

        if (isset($landmark['_skip'])) {
            $settings['skip']++;
            throw new Exception('skip');
        }
    }

    public function saveLandmark(array $landmark, int $start, int &$i, Content $content, array $data): array
    {
        $item = [
            "start" => $start,
            "end" => $i,
            "landmark" => $landmark,
            "data" => $data,
        ];
        if (isset($landmark["_block"])) {
            $i = $this->findBlocksEnd($landmark["_block"], $content, $i + 1);
            $item['end'] = $i;
        }
        return $item;
    }

    public function findBlocksEnd(array $blockSet, Content $content, int $start): int
    {
        list($instructions, $i) = $this->objectify($content, $blockSet['map'], $start);
        echo json_encode($instructions, JSON_PRETTY_PRINT);
        echo PHP_EOL . "start: " . $start . "(" . $content->getLetter($start) . "), end: " . $i . "(" . $content->getLetter($i) . ")" . PHP_EOL;
        return $i;
    }

    public function customPrepate(string $script)
    {
        $prepare = $this->schema['prepare'] ?? null;
        if (!isset($prepare) || !is_callable($prepare)) {
            return $script;
        }
        return $prepare($script);
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
            // skipping strings
            $i          = Str::skip($content->getLetter($i), $i, $content);
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
        foreach ($instructions as $instr => $block) {
            $maps[] =  $this->sliceIntoSteps($this->translateInstructionToMap($instr), $block);
        }
        $map = $this->mergeMaps($maps);

        return $map;
    }

    public function mergeMaps(array $maps, array $merged = []): array
    {
        foreach ($maps as $map) {
            $firstKey = array_key_first($map);
            if (isset($merged[$firstKey])) {
                $merged[$firstKey] = $this->mergeMaps([$map[$firstKey]], $merged[$firstKey]);
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

    public function sliceIntoSteps(array $map, array $block, int $stepCounter = 0): array|string
    {
        $types = [
            "landmark" => function (array $step) use ($map, $block, $stepCounter) {
                $res = $this->createWell($step['item'], $this->sliceIntoSteps($map, $block, $stepCounter + 1));
                return $res;
            },
            "method" => function (array $step) use ($map, $block, $stepCounter) {
                $steps = [];
                $nextStep = $this->sliceIntoSteps($map, $block, $stepCounter + 1);
                $methods = [];
                foreach ($step['item'] as $item) {
                    if ($item['name'] === 'e') {
                        $steps = array_merge($nextStep, $steps);
                        continue;
                    }
                    if (Validate::isStringLandmark($item['name'][0], '')) {
                        if (\mb_strlen($item['name']) !== 3) {
                            throw new Exception("OR literal method can be only made from 1 letter at time", 400);
                        }
                        $steps[trim($item['name'], $item['name'][0])] = $nextStep;
                    } else {
                        $methods[$item['name']] = $nextStep;
                        $this->methods[$item['name']] = $this->methodFromString($item['name']);
                    }
                }
                $steps['_m'] = $methods;
                return $steps;
            },
            "default" => function () use ($block) {
                $block["_stop"] = true;
                if (isset($block["_block"])) {
                    $block["_block"]['end'] =  $this->sliceIntoSteps(
                        $this->translateInstructionToMap($block["_block"]['end']),
                        [
                            "_finish" => true
                        ]
                    );
                    if (isset($block["_block"]['nested'])) {
                        $block["_block"]['nested'] =  $this->sliceIntoSteps(
                            $this->translateInstructionToMap($block["_block"]['nested']),
                            [
                                "_skip" => true
                            ]
                        );
                    }
                    $block["_block"]["map"] = [...$block["_block"]['end'], ...($block["_block"]['nested'] ?? [])];
                    unset($block["_block"]['end']);
                    unset($block["_block"]['nested']);
                }
                return $block;
            }
        ];

        return ($types[$map[$stepCounter]['type'] ?? null] ?? $types['default'])($map[$stepCounter] ?? null);
    }

    public function methodFromString(string $method): array
    {
        $content = new Content($method);
        $parameters = [];
        $name = '';
        $lastCutIndex = -1;
        for ($i=0; $i < $content->getLength(); $i++) {
            $i = Str::skip($content->getLetter($i), $i, $content);
            $letter = $content->getLetter($i);
            if ($letter === ":") {
                if (\strlen($name) == 0) {
                    $name = $content->iSubStr($lastCutIndex + 1, $i - 1);
                    if (Validate::isStringLandmark($name[0], '')) {
                        $name = trim($name, $name[0]);
                    }
                    $lastCutIndex = $i;
                    continue;
                }

                $param = $content->iSubStr($lastCutIndex + 1, $i - 1);
                if (Validate::isStringLandmark($param[0], '')) {
                    $param = trim($param, $param[0]);
                }
                $parameters[] = $param;
                $lastCutIndex = $i;
            }
        }

        $lastPart = $content->iSubStr($lastCutIndex + 1, $i - 1);
        if (strlen($lastPart) > 0) {
            if (Validate::isStringLandmark($lastPart[0], '')) {
                $lastPart = trim($lastPart, $lastPart[0]);
            }
            if (\strlen($name) == 0) {
                $name = $lastPart;
            } else {
                $parameters[] = $lastPart;
            }
        }

        return [
            "name" => $name,
            "params" => $parameters,
        ];
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
            $newI = Str::skip($instr->getLetter($i), $i, $instr);
            if ($i != $newI) {
                $currentItem .= $instr->iSubStr($i, $newI - 1);
                $i = $newI;
            }
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
