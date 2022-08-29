<?php declare(strict_types=1);

namespace Tetraquark;

use Tetraquark\Model\{CustomMethodEssentialsModel, LandmarkResolverModel, BlockModel};

/**
 *  Class for reading script and seperating it into managable blocks
 */
class Reader
{
    protected array $methods = [];
    protected array $script = [];
    protected array $map;
    protected array $current = [
        "caret" => null
    ];
    protected CustomMethodEssentialsModel $essentials;

    public const SKIP = 'skip';
    public const FINISH = 'finish';

    public function __construct(protected array $schema)
    {
        $this->essentials = new CustomMethodEssentialsModel();
    }

    public function read(string $script, bool $isPath = false)
    {
        if ($isPath) {
            if (!is_file($script)) {
                throw new Exception("Passed file was not found", 404);
            }

            $script = file_get_contents($script);
        }

        $content = $this->removeCommentsAndAdditional(new Content($script));
        $content = $this->customPrepare($content);
        // echo $content . PHP_EOL;
        // Log::log($content . '');
        Log::timerStart();
        $this->map = $this->generateBlocksMap();
        // die(json_encode($this->map, JSON_PRETTY_PRINT));
        list($this->script, $end) = $this->objectify($content, $this->map);
        Log::timerEnd();
        // echo json_encode($this->script, JSON_PRETTY_PRINT);
        $this->displayScriptBlocks($this->script);
    }

    public function displayScriptBlocks(array $script): void
    {
        Log::log('[');
        Log::increaseIndent();
        foreach ($script as $block) {
            Log::log('[');
            Log::increaseIndent();
            foreach ($block->toArray() as $key => $value) {
                if ($key === 'children') {
                    Log::log($key . ' => ');
                    Log::increaseIndent();
                    $this->displayScriptBlocks($value);
                    Log::decreaseIndent();
                } elseif ($key === 'parent' && !is_null($value)) {
                    Log::log($key . ' => parent[' . $value?->getIndex() . ']');
                } else {
                    Log::log($key . ' => ' . json_encode($value, JSON_PRETTY_PRINT) . ',', replaceNewLine: false);
                }
            }
            Log::decreaseIndent();
            Log::log('],');
        }
        Log::decreaseIndent();
        Log::log('],');
    }

    public function objectify(Content $content, array $map, int $start = 0, ?BlockModel $parent = null): array
    {
        $settings = new \stdClass();
        $settings->skip = 0;

        $landmarkResolver = new LandmarkResolverModel([
            "letter"     => null,
            "landmark"   => $map,
            "lmStart"    => null,
            "script"     => [],
            "i"          => null,
            "content"    => $content,
            "data"       => [],
            "settings"   => $settings,
            "map"        => $map,
            "parent"     => $parent,
        ]);

        try {
            for ($i=$start; $i < $content->getLength(); $i++) {
                try {
                    $landmarkResolver->setI(Str::skip($content->getLetter($i), $i, $content));
                    $landmarkResolver->setLetter($content->getLetter($i));

                    if (isset($landmarkResolver->getLandmark()[$landmarkResolver->getLetter()])) {
                        $this->resolveStringLandmark($landmarkResolver);
                        $i = $landmarkResolver->getI();
                        continue;
                    }

                    if (isset($landmarkResolver->getLandmark()['_m']) && $this->resolveMethodLandmark($landmarkResolver)) {
                        $i = $landmarkResolver->getI();
                        continue;
                    }

                    // If nothing was found but we have descended some steps into the map, try with the same letter from the start
                    if (!is_null($landmarkResolver->getLmStart())) {
                        $i--;
                    }

                    $this->clearObjectify($landmarkResolver);
                } catch (Exception $e) {
                    if ($e->getMessage() !== self::SKIP) {
                        throw $e;
                    }
                }
            }
        } catch (Exception $e) {
            if ($e->getMessage() !== self::FINISH) {
                throw $e;
            }
        }


        return [$landmarkResolver->getScript(), $landmarkResolver->getI()];
    }

    private function clearObjectify(LandmarkResolverModel $landmarkResolver)
    {
        $landmarkResolver->setLandmark($landmarkResolver->getMap());
        $landmarkResolver->setData([]);
        $landmarkResolver->setLmStart(null);
    }

    public function resolveStringLandmark(LandmarkResolverModel $landmarkResolver): void {
        $landmarkResolver->setLandmark(
            $landmarkResolver->getLandmark()[$landmarkResolver->getLetter()]
        );

        if (is_null($landmarkResolver->getLmStart())) {
            $landmarkResolver->setLmStart($landmarkResolver->getI());
        }

        if (isset($landmarkResolver->getLandmark()['_stop'])) {
            $this->resolveSettings($landmarkResolver);
            $landmarkResolver->setScript([...$landmarkResolver->getScript(), $this->saveLandmark($landmarkResolver)]);
            $this->clearObjectify($landmarkResolver);
        }
    }

    public function resolveMethodLandmark(LandmarkResolverModel $landmarkResolver): bool {
        foreach ($landmarkResolver->getLandmark()['_m'] as $methodName => $step) {
            $method = $this->methods[$methodName]
                ?? throw new Exception("Method " . htmlentities($methodName) . " not found", 404);

            $callable = $this->schema['methods'][$method['name']]
                ?? throw new Exception("Method " . htmlentities($methodName) . " not defined", 400);

            is_callable($callable)
                or throw new Exception("Method " . htmlentities($methodName) . " is not callable", 400);

            // Set essentials
            $essentials = [
                "content" => $landmarkResolver->getContent(),
                "letter"  => $landmarkResolver->getLetter(),
                "i"       => $landmarkResolver->getI(),
                "data"    => $landmarkResolver->getData(),
                "methods" => $this->schema['methods'],
            ];
            $skipReplace = ["methods" => true];
            $this->essentials->set($essentials);

            // Call method
            $res = $callable($this->essentials, ...$method['params']);

            // Update changed essentials
            foreach ($this->essentials as $key => $value) {
                if ($skipReplace[$key] ?? false) {
                    continue;
                }
                $getter = 'get' . Str::pascalize($key);
                $setter = 'set' . Str::pascalize($key);
                $landmarkResolver->$setter($this->essentials->$getter());
            }

            if (!$res) {
                continue;
            }

            $landmarkResolver->setLandmark($step);
            if (is_null($landmarkResolver->getLmStart())) {
                $landmarkResolver->setLmStart($landmarkResolver->getI());
            }
            if (isset($landmarkResolver->getLandmark()['_stop'])) {
                $this->resolveSettings($landmarkResolver);
                $landmarkResolver->setScript([...$landmarkResolver->getScript(), $this->saveLandmark($landmarkResolver)]);
                $this->clearObjectify($landmarkResolver);
            }
            return true;
        }
        return false;
    }

    public function resolveSettings(LandmarkResolverModel $landmarkResolver): void
    {
        if (isset($landmarkResolver->getLandmark()['_skip'])) {
            $landmarkResolver->getSettings()->skip++;
            throw new Exception(self::SKIP);
        }

        if ($landmarkResolver->getSettings()->skip > 0) {
            $landmarkResolver->getSettings()->skip--;
            throw new Exception(self::SKIP);
        }

        if (isset($landmarkResolver->getLandmark()['_finish'])) {
            throw new Exception(self::FINISH);
        }
    }

    public function saveLandmark(LandmarkResolverModel $landmarkResolver): BlockModel
    {
        $item = new BlockModel(
            start: $landmarkResolver->getLmStart(),
            end: $landmarkResolver->getI(),
            landmark: $this->clearLandmark($landmarkResolver->getLandmark()),
            data: $landmarkResolver->getData(),
            index: \sizeof($landmarkResolver->getScript()),
            parent: $landmarkResolver->getParent()
        );

        $block = $landmarkResolver->getLandmark()["_block"] ?? false;

        if ($block) {
            list($i, $blocks) = $this->findBlocksEnd($block, $landmarkResolver->getContent(), $landmarkResolver->getI() + 1, $item);
            $landmarkResolver->setI($i);
            $item->setEnd($i);
            $item->setChildren($blocks);
        }
        // Variable normally share their end/start:
        // `let a = 'a'\nlet b = 'd'` (variable a is sharing its end (`\n`) with variable b)
        // `let a = 'a';let b = 'd'` (variable a is sharing its end (`;`) with variable b)
        // so we will try to include the last letter once more
        $landmarkResolver->i--;

        return $item;
    }

    public function clearLandmark(array $landmark): array
    {
        foreach ($landmark as $key => $value) {
            if (is_string($key) && $key[0] == '_') {
                unset($landmark[$key]);
            }
        }
        return $landmark;
    }

    public function findBlocksEnd(array $blockSet, Content $content, int $start, BlockModel $parent): array
    {
        list($instructions, $i) = $this->objectify($content, $blockSet['map'], $start, $parent);
        if (is_null($this->current['caret'])) {
            $this->current['caret'] = 0;
        }
        $this->current['caret'] += $start;
        $caretIncr = $this->current['caret'];
        $newContent = $content->iCutToContent($start, $i);
        if (!Validate::isWhitespace($newContent->getLetter(0))) {
            $newContent->prependArrayContent([" "]);
        }
        list($blocks) = $this->objectify($newContent, $this->map, parent: $parent);
        foreach ($blocks as &$block) {
            $block->setStart($block->getStart() + $caretIncr);
            $block->setEnd($block->getEnd() + $caretIncr);
        }
        $this->current['caret'] = null;
        return [$i, $blocks];
    }

    public function customPrepare(Content $content): Content
    {
        $prepare = $this->schema['prepare'] ?? null;
        if (!isset($prepare) || !is_callable($prepare)) {
            return $content;
        }
        return $prepare($content);
    }

    public function removeCommentsAndAdditional(Content $content): Content
    {
        if (
            (
                sizeof($this->schema['comments']) == 0
                || ($this->schema['remove']['comments'] ?? false) == false
            ) && !isset($this->schema['remove']['additional'])
        ) {
            return $content;
        }

        $comment = [
            "schema" => ($this->schema['remove']['comments'] ?? false) ? ($this->schema['comments']) : [],
            "start"  => null,
            "map"    => null
        ];

        $additional = $this->schema['remove']['additional'] ?? null;

        for ($i=0; $i < $content->getLength(); $i++) {
            // Skipping strings
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
            foreach ($map as $key => $value) {
                if (isset($merged[$key])) {
                    $merged[$key] = $this->mergeMaps([$map[$key]], $merged[$key]);
                } else {
                    $merged[$key] = $map[$key];
                }
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
                if (empty($param)) {
                    $param = null;
                } elseif (Validate::isStringLandmark($param[0], '')) {
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

                    $item = $this->createMapItem($methods, "method");
                    if (!is_null($item)) {
                        $map[] = $item;
                    }

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
                $item = $this->createMapItem($currentItem, "landmark");
                if (!is_null($item)) {
                    $map[] = $item;
                }
                $currentItem = '';
                $inMethod = true;
                continue;
            }

            if ($inMethod && $letter === '|') {
                $methods[] = $this->createMethodMapItem($letter, $lastMethodLandmark, $currentItem);
                $currentItem = '';
                continue;
            }

            $currentItem .= $letter;
        }

        if (strlen($currentItem) > 0) {
            $item = $this->createMapItem($currentItem, "landmark");
            if (!is_null($item)) {
                $map[] = $item;
            }
        }

        return $map;
    }

    private function createMapItem(string|array $item, string $type): ?array
    {
        if (empty($item)) {
            return null;
        }
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
