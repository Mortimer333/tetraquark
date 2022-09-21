<?php declare(strict_types=1);

namespace Tetraquark;

use Tetraquark\Model\{
    CustomMethodEssentialsModel,
    LandmarkResolverModel,
    Block\BlockModel,
    Block\ScriptBlockModel
};
use Tetraquark\Contract\BlockModelInterface;
use Tetraquark\Factory\ClosureFactory;

/**
 *  Class for reading script and seperating it into managable blocks
 */
class Reader
{
    protected array $schemaDefaults = [
        "shared" => [
            "ends" => []
        ],
        "comments" => [],
        "remove" => [
            "comments" => false,
        ],
        "instructions" => [],
        "methods" => [],
        "prepare" => null,
    ];
    protected array $methods = [];
    protected array $script = [];
    protected array $map;
    protected array $current = [
        "caret" => null
    ];
    protected CustomMethodEssentialsModel $essentials;
    protected int $iterations = 0;
    protected array $notClear = [
        "_missed" => true,
    ];
    protected array $debug = [
        "path" => []
    ];

    public const SKIP = 'skip';
    public const FINISH = 'finish';
    public const END_OF_FILE = 'end of file';
    public const EMPTY_METHOD = 'e';

    public function __construct(protected array $schema)
    {
        $this->essentials = new CustomMethodEssentialsModel();
        $this->schema = $this->schemaSetDefaults($this->schema, $this->schemaDefaults);
    }

    public function schemaSetDefaults(array $schema, array $defaults): array
    {
        foreach ($defaults as $key => $value) {
            if (isset($schema[$key])) {
                if (is_array($value)) {
                    $schema[$key] = $this->schemaSetDefaults($schema[$key], $value);
                }
            } else {
                $schema[$key] = $value;
            }
        }

        return $schema;
    }

    public function read(string $script, bool $isPath = false)
    {
        if ($isPath) {
            if (!is_file($script)) {
                throw new Exception("Passed file was not found", 404);
            }

            $script = file_get_contents($script);
        }
        // @TODO think this one through
        // $script = str_replace("\r\n", "\n", $script);

        $content = $this->removeCommentsAndAdditional(new Content($script));
        $content = $this->customPrepare($content);

        // echo $content . PHP_EOL;
        Log  ::log($content . '');
        Log  ::timerStart();
        $this->map = $this->generateBlocksMap();
        // die(json_encode($this->methods, JSON_PRETTY_PRINT));
        // die(json_encode($this->map, JSON_PRETTY_PRINT));
        $script = new ScriptBlockModel();
        list($this->script, $end) = $this->objectify($content, $this->map, parent: $script);
        Log  ::timerEnd();
        // echo json_encode($this->script, JSON_PRETTY_PRINT);
        $this->displayScriptBlocks($this->script);
    }

    public function displayScriptBlocks(array $script): void
    {
        Log  ::log('[');
        Log  ::increaseIndent();
        foreach ($script as $block) {
            Log  ::log('[');
            Log  ::increaseIndent();
            foreach ($block->toArray() as $key => $value) {
                if ($key === 'children') {
                    Log  ::log($key . ' => ');
                    Log  ::increaseIndent();
                    $this->displayScriptBlocks($value);
                    Log  ::decreaseIndent();
                } elseif ($key === 'parent' && !is_null($value)) {
                    if ($value instanceof ScriptBlockModel) {
                        Log  ::log($key . ' => script');
                    } else {
                        Log  ::log($key . ' => parent[' . $value?->getIndex() . ']');
                    }
                } else {
                    if ($key == 'landmark') {
                        Log  ::log($key . ' => ' . json_encode($value['_custom'] ?? [], JSON_PRETTY_PRINT) . ',', replaceNewLine: false);
                    } else {
                        Log  ::log($key . ' => ' . json_encode($value, JSON_PRETTY_PRINT) . ',', replaceNewLine: false);
                    }
                }
            }
            Log  ::decreaseIndent();
            Log  ::log('],');
        }
        Log  ::decreaseIndent();
        Log  ::log('],');
    }

    public function objectify(Content $content, array $map, int $start = 0, ?BlockModelInterface $parent = null): array
    {
        $settings = new \stdClass();
        $settings->skip = 0;

        $resolver = new LandmarkResolverModel([
            "letter"     => null,
            "landmark"   => $map,
            "lmStart"    => null,
            "script"     => [],
            "i"          => $start,
            "content"    => $content,
            "data"       => [],
            "settings"   => $settings,
            "map"        => $map,
            "parent"     => $parent,
        ]);

        try {
            for ($i=$start; $i < $content->getLength(); $i++) {
                try {
                    $this->resolve($resolver, $i);
                } catch (Exception $e) {
                    if ($e->getMessage() !== self::SKIP) {
                        throw $e;
                    }
                }
            }
        } catch (Exception $e) {
            if ($e->getMessage() !== self::FINISH && $e->getMessage() !== self::END_OF_FILE) {
                throw $e;
            }
        }


        if ($resolver->getI() === $resolver->getContent()->getLength() - 1) {
            $last = $resolver->getParent()->getLastChild();
            if (!$last) {
                $this->addMissedEnd($resolver, 0, $resolver->getContent()->getLength());
            } elseif ($last?->getEnd() + 1 < $resolver->getContent()->getLength()) {
                $this->addMissedEnd($resolver, $last->getEnd() + 1, $resolver->getContent()->getLength());
            }
        }

        return [$resolver->getScript(), $resolver->getI()];
    }

    public function addMissedEnd(LandmarkResolverModel $resolver, int $start, int $end): void
    {
        $last = $resolver->getParent()->getLastChild();
        $data = $this->getMissedData($resolver, $start, $end);
        if ($data['missed'] == '' || Validate::isWhitespace($data['missed'])) {
            return;
        }
        $resolver->setLmStart($start);
        $resolver->setI($end);
        $resolver->setLandmark($this->getMissedLandmark());
        $resolver->setData($data);
        $this->saveBlock($resolver);
    }

    public function getMissedData(LandmarkResolverModel $resolver, int $start, int $end): array
    {
        Log::log('Get missed: ' . $start . ', ' . $end . ' - Start: ' . $resolver->getContent()->getLetter($start) . ' End: ' . $resolver->getContent()->getLetter($end));
        $missed = $resolver->getContent()->iSubStr($start, $end);
        return [
            "missed" => $missed
        ];
    }

    private function resolve(LandmarkResolverModel $resolver, int &$i)
    {
        // @TODO remove this, and think of better fail save
        $this->iterations++;
        if ($this->iterations > 2000) {
            throw new \Error('Inifinite loop');
        }

        $content = $resolver->getContent();
        if (is_null($content->getLetter($i))) {
            /* Don't end file with exception but let it slowly get out of foreach */
            // $resolver->i--;
            // throw new Exception(self::END_OF_FILE);
            return false;
        }

        // Don't skip string - $resolver->setI(Str::skip($content->getLetter($i), $i, $content));
        $resolver->setI($i);
        $resolver->setLetter($content->getLetter($i));
        // // Log ::log($i . ' Letter: `' . $resolver->getLetter() . '`, `' . $resolver->getLmStart() . '`, ' . $resolver->getContent()->getLength() . ', possible: ' . implode(', ', array_keys($resolver->getLandmark())));
        if (isset($resolver->getLandmark()[$resolver->getLetter()])) {
            $res = $this->resolveStringLandmark($resolver);
            if ($res) {
                $i = $resolver->getI();
                return true;
            }
        }

        if (isset($resolver->getLandmark()['_m']) && $this->resolveMethodLandmark($resolver)) {
            $i = $resolver->getI();
            return true;
        }

        /* @DOUBLE_CHECK this operation might be unnecessary, currently I can't think of example where this helps but it is quite late at night */
        // If nothing was found but we have descended some steps (more then one) into the map, try with the same letter from the start
        if (!is_null($resolver->getLmStart()) && sizeof($this->debug['path']) > 1) {
            $i--;
            $resolver->setLetter($content->getLetter($i));
        }

        $this->clearObjectify($resolver);

        return false;
    }

    private function clearObjectify(LandmarkResolverModel $resolver)
    {
        $resolver->setLandmark($resolver->getMap());
        $resolver->setData([]);
        $resolver->setLmStart(null);
        $this->debug["path"] = [];
    }

    public function resolveStringLandmark(LandmarkResolverModel $resolver): bool
    {
        $this->debug["path"][] = $resolver->getLetter();
        $possibleLandmark = $resolver->getLandmark()[$resolver->getLetter()];

        // // Log ::log('New string lm, oprions: ' . implode(', ', array_keys($possibleLandmark)));
        if (is_null($resolver->getLmStart())) {
            $resolver->setLmStart($resolver->getI());
        }

        if (isset($possibleLandmark['_stop'])) {
            $resolver->setLandmark($possibleLandmark);
            $this->resolveSettings($resolver);
            $this->saveBlock($resolver);
            return true;
        }
        $res = $this->tryToFindNextMatch($resolver, $possibleLandmark);
        if ($res) {
            return true;
        }

        return false;
    }

    public function getMethod(string $methodName): array
    {
        $method = $this->methods[$methodName]
            ?? throw new Exception("Method " . htmlentities($methodName) . " not found", 404);

        $callable = $this->schema['methods'][$method['name']]
            ?? throw new Exception("Method " . htmlentities($methodName) . " not defined", 400);

        is_callable($callable)
            or throw new Exception("Method " . htmlentities($methodName) . " is not callable", 400);

        return [$method, $callable];
    }

    public function resolveMethodLandmark(LandmarkResolverModel $resolver): bool
    {
        foreach ($resolver->getLandmark()['_m'] as $methodName => $step) {
            list($method, $callable) = $this->getMethod($methodName);
            // Set essentials
            $essentials = [
                "content"  => $resolver->getContent(),
                "lmStart"  => $resolver->getLmStart(),
                "letter"   => $resolver->getLetter(),
                "i"        => $resolver->getI(),
                "data"     => $resolver->getData(),
                "previous" => $resolver->getParent()?->getLastChild(),
                "methods"  => $this->schema['methods'],
            ];
            $skipReplace = [
                "methods" => true,
                "previous" => true,
                "lmStart" => true,
            ];
            $this->essentials->set($essentials);

            // Call method
            $res = $callable($this->essentials, ...$method['params']);

            if (!$res) {
                continue;
            }

            $this->debug["path"][] = $methodName;

            $save = $this->saveResolver($resolver, []);
            $this->resolveThen($this->essentials, $method);

            if (is_null($resolver->getLmStart())) {
                $resolver->setLmStart($resolver->getI());
            }

            // Update changed essentials
            foreach ($this->essentials as $key => $value) {
                if ($skipReplace[$key] ?? false) {
                    continue;
                }
                $getter = 'get' . Str::pascalize($key);
                $setter = 'set' . Str::pascalize($key);
                $resolver->$setter($this->essentials->$getter());
            }

            // // Log ::log('New method `' . $methodName . '` lm, oprions: ' . implode(', ', array_keys($step)));

            if (isset($step['_stop'])) {
                $resolver->setLandmark($step);
                $this->resolveSettings($resolver);
                $this->saveBlock($resolver);
                $this->clearObjectify($resolver);
                return true;
            }

            $res = $this->tryToFindNextMatch($resolver, $step);
            if ($res) {
                return true;
            }
            $this->restoreResolver($resolver, $save);
        }
        return false;
    }

    public function resolveThen(CustomMethodEssentialsModel $essentials, array $method): void
    {
        if (!isset($method["then"])) {
            return;
        }

        list($method, $callable) = $this->getMethod($method['then']);
        $callable($this->essentials, ...$method['params']);

        $this->resolveThen($essentials, $method);
    }

    public function tryToFindNextMatch(LandmarkResolverModel $resolver, array $posLandmark): bool
    {
        $save = $this->saveResolver($resolver, []);
        $resolver->setLandmark($posLandmark);
        $resolver->i++;
        $res = $this->resolve($resolver, $resolver->i);
        if ($res) {
            return true;
        }
        $this->restoreResolver($resolver, $save);
        return false;
    }

    public function resolveSettings(LandmarkResolverModel $resolver): void
    {
        if (isset($resolver->getLandmark()['_skip'])) {
            $resolver->getSettings()->skip++;
            throw new Exception(self::SKIP);
        }

        if ($resolver->getSettings()->skip > 0) {
            $resolver->getSettings()->skip--;
            throw new Exception(self::SKIP);
        }

        if (isset($resolver->getLandmark()['_finish'])) {
            $this->saveBlock($resolver);
            throw new Exception(self::FINISH);
        }
    }

    public function clearLandmark(array $landmark): array
    {
        foreach ($landmark as $key => $value) {
            if (
                ($key[0] === '_' && !isset($this->notClear[$key]))
                || (is_array($value) && isset($value['_stop']))
            ) {
                unset($landmark[$key]);
                continue;
            }
        }
        return $landmark;
    }

    public function saveResolver(LandmarkResolverModel $resolver, array $remove = []): array
    {
        $save = $resolver->toArray();
        foreach ($remove as $value) {
            unset($save[$value]);
        }
        return $save;
    }

    public function restoreResolver(LandmarkResolverModel $resolver, array $save): void
    {
        $resolver->set($save);
    }

    public function saveBlock(LandmarkResolverModel $resolver): void
    {
        $debug = $this->debug['path'];
        // Check next letters if we don't have more specified block which matches syntax.
        // More specified in a way that his definition is longer/more detailed
        try {
            $i = $resolver->getI() + 1;
            // @POSSIBLE_PERFORMANCE_ISSUE
            $save = $this->saveResolver($resolver);
            $res = $this->resolve($resolver, $i);
            if ($res) {
                return;
            }
            $this->restoreResolver($resolver, $save);
        } catch (\Exception $e) {
            if ($e->getMessage() !== self::FINISH && $e->getMessage() !== self::END_OF_FILE) {
                throw $e;
            }
        }
        $this->debug['path'] = $debug;

        // // Log ::log('Save block - ' . json_encode($resolver->getLandmark()['_custom'] ?? []) . ", debug: " . implode(' => ', $this->debug['path']));
        $item = new BlockModel(
            start: $resolver->getLmStart(),
            end: $resolver->getI(),
            landmark: $resolver->getLandmark(),
            data: $resolver->getData(),
            index: \sizeof($resolver->getScript()),
            parent: $resolver->getParent()
        );

        $block = $item->getLandmark()["_block"] ?? false;

        if ($block) {
            $item->setBlockStart($item->getEnd());
            list($i, $blocks) = $this->findBlocksEnd($block, $resolver->getContent(), $item->getEnd() + 1, $item);
            $resolver->setI($i);
            $item->setEnd($i);
            $item->setChildren($blocks);
        }

        // Some variable normally share their end/start:
        // `let a = 'a'\nlet b = 'd'` (variable a is sharing its end (`\n`) with variable b)
        // `let a = 'a';let b = 'd'` (variable a is sharing its end (`;`) with variable b)
        // so we will try to include the last letter once more.
        // But we don't do it for Blocks with instruction of length 1
        if (
            $item->getStart() !== $item->getEnd()
            && ($this->schema['shared']['ends'][
                $resolver->getContent()->getLetter(
                    $item->getEnd()
                )
            ] ?? false)
        ) {
            $resolver->i--;
        }
        $resolver->getParent()->addChild($item);

        // @POSSIBLE_PERFORMANCE_ISSUE
        $resolver->setScript([...$resolver->getScript(), $item]);

        $this->addMissed($resolver, $item);

        // Exception if no child was found
        if ($block && sizeof($item->getChildren()) === 0) {
            Log::log('Check missed: ' . $item->getBlockStart() . ', ' . $item->getBlockEnd() . ' - Start: ' . $resolver->getContent()->getLetter($item->getBlockStart()) . ' End: ' . $resolver->getContent()->getLetter($item->getBlockEnd()));
            $start = $item->getBlockStart() + 1;
            $end = $item->getBlockEnd() - 1;

            if ($end < $start) {
                return;
            }

            $inside = $resolver->getContent()->iSubStr($start, $end);
            if (strlen($inside) != 0 && !Validate::isWhitespace($inside)) {
                $data = $this->getMissedData($resolver, $start, $end);

                $child = new BlockModel(
                    start: $start,
                    end: $end,
                    landmark: $this->getMissedLandmark(),
                    data: $data,
                    index: 0,
                    parent: $resolver->getParent(),
                );

                $item->addChild($child);
            }
        }
        $this->clearObjectify($resolver);
    }

    public function getMissedLandmark(): array
    {
        return [
            "_missed" => true,
        ];
    }

    public function addMissed(LandmarkResolverModel $resolver, BlockModelInterface $parent): void
    {
        $script = $resolver->getScript();
        $scriptLen = \sizeof($script);

        if ($scriptLen <= 0) {
            return;
        } elseif ($scriptLen == 1) {
            $lastChild = $script[$scriptLen - 1];
            $end = $lastChild->getStart() - 1;
            $start = $index = 0;
        } else {
            $lastChild = $script[$scriptLen - 1];
            $secondLastChild = $script[$scriptLen - 2];
            $start = $secondLastChild->getEnd() + 1;
            $end = $lastChild->getStart() - 1;
            $index = $lastChild->getIndex();
        }

        if ($end >= $start) {
            $data = $this->getMissedData($resolver, $start, $end);
            if (Validate::isWhitespace($data['missed'])) {
                return;
            }

            $item = new BlockModel(
                start: $start,
                end: $end,
                landmark: $this->getMissedLandmark(),
                data: $data,
                index: $index,
                parent: $parent,
            );

            array_splice($script, $index, 0, [$item]);
            if (isset($lastChild)) {
                $lastChild->setIndex($index + 1);
            }

            $resolver->setScript($script);
        }
    }

    public function findBlocksEnd(array $blockSet, Content $content, int $start, BlockModelInterface $parent): array
    {
        // We firstly search for the end of the block then map contents to ensure that small error in the block won't break the whole chain
        // and we will be able to use this data i.e. to point more then one error at time

        // Find the end of block
        list($endBlocks, $i) = $this->objectify($content, $blockSet['map'], $start, $parent);
        // if (empty($content->getLetter($i))) {
        //     Log::log($content->subStr($start));
        //     Log::log($blockSet['map'], replaceNewLine: false);
        //     Log::log('Endoffile');
        //     $i--;
        // }

        if (sizeof($endBlocks) > 0) {
            $endBlock = $endBlocks[sizeof($endBlocks) - 1];

            // End of the block's content
            if ((!isset($blockSet["include_end"]) || !$blockSet["include_end"]) && !isset($endBlock->getLandmark()['_missed'])) {
                $i = $endBlock->getStart() - 1;
            }

            $data = [];
            if (!isset($endBlock->getLandmark()['_missed'])) {
                $data = $endBlock->getData();
            }
            Log::log('GEt end');
            $end = $endBlock->getEnd();
        } else {
            Log::log('Set end');
            $end = $i;
            $data = [];
        }

        $parent->setBlockEnd($i);
        $parent->setData([...$parent->getData(), ...["_end" => $data]]);

        if (is_null($this->current['caret'])) {
            $this->current['caret'] = 0;
        }

        $this->current['caret'] += $start;
        $caretIncr  = $this->current['caret'];
        $newContent = $content->iCutToContent($start, $i);
        $blocks     = [];

        if ($newContent->getLength() !== 0) {
            if (!Validate::isWhitespace($newContent->getLetter(0))) {
                $newContent->prependArrayContent([" "]);
            }
            // Generate blocks
            list($blocks) = $this->objectify($newContent, $this->map, parent: $parent);

            foreach ($blocks as &$block) {
                $block->setStart($block->getStart() + $caretIncr);
                $block->setEnd($block->getEnd() + $caretIncr);
                if (!is_null($block->getBlockStart()) && !is_null($block->getBlockEnd())) {
                    $block->setBlockStart($block->getBlockStart() + $caretIncr);
                    $block->setBlockEnd($block->getBlockEnd() + $caretIncr);
                }
            }
        }
        $this->current['caret'] = null;
        // Real end of the block
        return [$end, $blocks];
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
        $instrKeys = array_keys($instructions);
        for ($i=0; $i < \sizeof($instructions); $i++) {
            $instr = $instrKeys[$i];
            $block = $instructions[$instr];
            if ($block['_extend'] ?? false) {
                foreach ($block['_extend'] as $subInstr => $subBlock) {
                    $instructions[$instr . $subInstr] = $subBlock;
                    $instrKeys[] = $instr . $subInstr;
                }
                unset($instructions[$instr]["_extend"]);
                if (empty($instructions[$instr])) {
                    unset($instructions[$instr]);
                }
            }
        }

        foreach ($instructions as $instr => $block) {
            $maps[] = $this->sliceIntoSteps(
                $this->translateInstructionToMap($instr),
                $this->encloseCustomData($block)
            );
        }
        $map = $this->mergeMaps($maps);

        return $map;
    }

    public function encloseCustomData(array $block): array
    {
        $toEnclose = [];
        foreach ($block as $key => $value) {
            if (is_numeric($key) || $key[0] !== '_') {
                $toEnclose[$key] = $value;
                unset($block[$key]);
            }
        }
        $block['_custom'] = $toEnclose;
        if (isset($block['_expand'])) {
            foreach ($block['_expand'] as $key => $subBlock) {
                $block['_expand'][$key] = $this->encloseCustomData($subBlock);
            }
        }
        return $block;
    }

    public function mergeMaps(array $maps, array $merged = [], bool $continue = false): array
    {
        foreach ($maps as $map) {
            foreach ($map as $key => $value) {
                if (!isset($value['_stop'])) {
                    $merged[$key] = $this->mergeMaps([$map[$key]], $merged[$key] ?? []);
                }
                if (is_array($map[$key])) {
                    $merged[$key] = array_merge($map[$key], $merged[$key] ?? []);
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
                    $name = $item['method'];
                    // Sepcial method - empty
                    if ($name === self::EMPTY_METHOD) {
                        if (isset($nextStep["_m"])) {
                            $methods = array_merge($methods, $nextStep["_m"]);
                            unset($nextStep["_m"]);
                        }
                        $steps = array_merge($nextStep, $steps);
                        continue;
                    }
                    if (Validate::isStringLandmark($name[0], '')) {
                        $strLen = \mb_strlen($name);
                        if ($strLen !== 3 && $strLen !== 4) {
                            throw new Exception("OR literal method can be only made from 1 letter at time (optionaly with negation `!`)" . $item['name'], 400);
                        }
                        $name = trim($name, $name[0]);
                        if ($strLen === 4 && $name[0] === '!') {
                            if (!isset($item['_skip']) || !$item['_skip']) {
                                $methods                [$name] = $nextStep;
                            }
                            $this->methods          [$name] = $item;
                            $this->schema['methods'][$name] = ClosureFactory::generateReversalClosure($name[1]);
                        } else {
                            $steps[$name] = $nextStep;
                        }
                    } else {
                        if (!isset($item['_skip']) || !$item['_skip']) {
                            $methods[$name] = $nextStep;
                        }
                        $this->methods[$name] = $item;
                    }
                }

                $steps['_m'] = $methods;
                return $steps;
            },
            "default" => function () use ($block) {
                $block["_stop"] = true;
                if (isset($block["_block"])) {

                    if (is_string($block["_block"]['end'])) {
                        $block["_block"]['end'] = [$block["_block"]['end']];
                    }
                    $block["_block"]['end'] = $this->sliceToMap($block["_block"]['end'], ["_finish" => true]);

                    if (isset($block["_block"]['nested'])) {
                        if (is_string($block["_block"]['nested'])) {
                            $block["_block"]['nested'] = [$block["_block"]['nested']];
                        }
                        $block["_block"]['nested'] = $this->sliceToMap($block["_block"]['nested'], ["_skip" => true]);
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

    public function sliceToMap(array $instructions, array $data): array
    {
        $endMaps = [];
        foreach ($instructions as $value) {
            $endMaps[] = $this->sliceIntoSteps(
                $this->translateInstructionToMap($value),
                $data
            );
        }
        return $this->mergeMaps($endMaps);
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
                } else {
                    $param = $this->transformParam($param);
                }

                $parameters[] = $param;
                $lastCutIndex = $i;
            }
        }

        $lastPart = $content->iSubStr($lastCutIndex + 1, $i - 1);
        if (strlen($lastPart) > 0) {
            if (Validate::isStringLandmark($lastPart[0], '')) {
                $lastPart = trim($lastPart, $lastPart[0]);
            } else {
                $lastPart = $this->transformParam($lastPart);
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

    public function transformParam(string $param): mixed
    {
        $transformTable = [
            "false" => false,
            "true" => true,
            "null" => null,
        ];
        return $transformTable[$param] ?? $param;
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
        $inMethod = false;
        for ($i=0; $i < $instr->getLength(); $i++) {
            if ($inMethod) {
                $newI = Str::skip($instr->getLetter($i), $i, $instr);
                if ($i != $newI) {
                    $currentItem .= $instr->iSubStr($i, $newI - 1);
                    $i = $newI;
                }
            }
            $letter = $instr->getLetter($i);
            // skip this letter and just add next one
            if ($letter === "\\") {
                if ($inMethod) {
                    if (strlen($currentItem) > 0) {
                        $methods = array_merge($methods, $this->createMethodMapItems($currentItem));
                    }

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
                $methods = array_merge($methods, $this->createMethodMapItems($currentItem));
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

    private function createMethodMapItems(string $currentItem, bool $skip = false): array
    {
        $specials = [
            '>' => "then",
        ];

        $content = new Content($currentItem);
        $name = $currentItem;
        $rest = '';
        for ($i=0; $i < $content->getLength(); $i++) {
            $symbol = $content->getLetter($i);
            if ($specials[$symbol] ?? null) {
                $name = $content->iSubStr(0, $i - 1);
                $rest = $content->subStr($i + 1);
                $special = $specials[$symbol];
                break;
            }
        }

        $method = $currentItem;
        if ($skip) {
            $method .= "_skip";
        }

        $item = [
            ...$this->methodFromString($name),
            "method" => $method,
        ];

        if ($skip) {
            $item['_skip'] = true;
        }

        $items = [$item];
        if (isset($special)) {
            $item[$special] = $rest . '_skip';
            $items = [...$this->createMethodMapItems($rest, true), $item];
        }

        return $items;
    }
}
