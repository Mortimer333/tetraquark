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
        "prepare" => [
            "content" => null,
            "missed" => null
        ],
    ];
    protected static array $compiled = [];
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
    // Comment found inbetween blocks
    protected array $comments = [];
    // Retrieve comments and replace them with whitespace
    protected bool $retrieveComments = true;
    // Save retrieved comments
    protected bool $setRetrievedComments = true;

    public const SKIP = 'skip';
    public const FINISH = 'finish';
    public const END_OF_FILE = 'end of file';
    public const EMPTY_METHOD = 'e';

    public function __construct(
        protected string $path,
        protected bool $cache = true,
    ) {
        if (!is_file($path)) {
            throw new Exception("Given schema doesn't exist", 400);
        }

        $this->schema = require $path;
        $this->schema = $this->schemaSetDefaults($this->schema, $this->schemaDefaults);

        if ($this->cache && isset(self::$compiled[$this->path])) {
            $this->map = self::$compiled[$this->path]['map'];
            $this->methods = self::$compiled[$this->path]['methods'];
        } else {
            $this->map = $this->generateBlocksMap();
            self::$compiled[$this->path]['map'] = $this->map;
            self::$compiled[$this->path]['methods'] = $this->methods;
        }

        // die(json_encode($this->methods, JSON_PRETTY_PRINT));
        // die(json_encode($this->map, JSON_PRETTY_PRINT));
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

    public function read(string $script, bool $isPath = false, bool $displayBlocks = false, ?bool $short = null)
    {
        if (is_null($short)) {
            $short = $displayBlocks;
        }
        if ($isPath) {
            if (!is_file($script)) {
                throw new Exception("Passed file was not found", 404);
            }

            $script = file_get_contents($script);
        }
        // @TODO think this one through
        // $script = str_replace("\r\n", "\n", $script);
        if ($displayBlocks || $short) {
            Log  ::timerStart();
        }

        $content = $this->removeCommentsAndAdditional(new Content($script));
        $content = $this->customPrepare($content);
        // echo $content . PHP_EOL;
        if ($displayBlocks) {
            Log  ::log($content . '');
        }
        $script = new ScriptBlockModel();
        list($script, $end) = $this->objectify($content, $this->map, parent: $script);
        // echo json_encode($script, JSON_PRETTY_PRINT);
        if ($displayBlocks || $short) {
            Log  ::timerEnd();
        }

        if ($short) {
            $this->displayLandmarks($script);
        }

        if ($displayBlocks) {
            Log  ::log('[');
            $this->displayScriptBlocks($script);
            Log  ::log(']');
        }
        return $script;
    }

    public function setRerieveComments(bool $retrieveComments): self
    {
        $this->retrieveComments = $retrieveComments;
        return $this;
    }

    public function getRerieveComments(): bool
    {
        return $this->retrieveComments;
    }

    public function setSaveRerievedComments(bool $setRetrievedComments): self
    {
        $this->setRetrievedComments = $setRetrievedComments;
        return $this;
    }

    public function getSaveRerievedComments(): bool
    {
        return $this->setRetrievedComments;
    }

    public function setComments(array $comments): self
    {
        $this->comments = $comments;
        return $this;
    }

    public function getComments(): array
    {
        return $this->comments;
    }

    public function displayLandmarks(array $script): void
    {
        $landmarks = $this->getLandmarks($script);
        $this->showLandmark($landmarks);
    }

    public function showLandmark(array $landmarks): void
    {
        Log  ::log($landmarks['landmark']);
        if (!empty($landmarks['children'])) {
            Log  ::increaseIndent();
            foreach ($landmarks['children'] as $child) {
                $this->showLandmark($child);
            }
            Log  ::decreaseIndent();
        }
    }

    public function getLandmarks(array $script): array
    {
        $landmarks = [
            "landmark" => '',
            "data" => [],
            "children" => []
        ];

        foreach ($script['data'] ?? [] as $key => $value) {
            if (!is_array($value)) {
                $landmarks["data"][] = $key . ': `' . $value . '`';
            }
        }

        foreach ($script as $key => $block) {
            if ($key == 'parent') {
                continue;
            }
            if ($key === 'landmark') {
                $landmark = $block['_custom']['class'] ?? 'No class';
                $landmark .= ' {';
                unset($block['_custom']['class']);
                $i = 0;
                foreach ($block['_custom'] ?? [] as $key => $value) {
                    if ($i === 0) {
                        $landmark .= $key . ': ' . $value;
                    } else {
                        $landmark .= ', ' . $key . ': ' . $value;
                    }
                    $i++;
                }
                $landmark .= '}';
                $landmarks["landmark"] = $landmark ?? 'No class';
                $block = $block['_custom'] ?? [];
            }
            if ($block instanceof BlockModel) {
                $block = $block->toArray();
            }
            if (is_array($block)) {
                $res = $this->getLandmarks($block);
                if (empty($res['landmark'])) {
                    $landmarks["children"] = array_merge($landmarks["children"], $res['children']);
                } else {
                    $landmarks["children"][] = $res;
                }
            }
        }
        if (!empty($landmarks['data'])) {
            $landmarks['landmark'] .= ' [' . implode(', ', $landmarks['data']) . ']';
        }
        return $landmarks;
    }

    public function displayScriptBlocks(array $script, bool $item = true): void
    {
        Log  ::increaseIndent();
        foreach ($script as $key => $block) {
            if (is_array($block) || $block instanceof BlockModel) {
                Log  ::log('[');
                Log  ::increaseIndent();
            }
            if ($block instanceof BlockModel) {
                $this->displayBlock($block);
            } else if (is_array($block)) {
                Log  ::log('"' . $key . '" => [');
                $this->displayScriptBlocks($block, true);
                Log  ::log('],');
            } else {
                if (is_numeric($key)) {
                    Log  ::log(json_encode($block, JSON_PRETTY_PRINT) . ',', replaceNewLine: false);
                } else {
                    Log  ::log('"' . $key . '" => ' . json_encode($block, JSON_PRETTY_PRINT) . ',', replaceNewLine: false);
                }
            }
            if (is_array($block) || $block instanceof BlockModel) {
                Log  ::decreaseIndent();
                Log  ::log('],');
            }
        }
        Log  ::decreaseIndent();
    }

    public function displayBlock(BlockModel $block): void
    {
        foreach ($block->toArray() as $key => $value) {
            if ($key === 'children') {
                Log  ::log('"' . $key . '" => [');
                $this->displayScriptBlocks($value);
                Log  ::log('],');
            } elseif ($key === 'parent' && !is_null($value)) {
                if ($value instanceof ScriptBlockModel) {
                    Log  ::log('"' . $key . '" => script');
                } else {
                    Log  ::log('"' . $key . '" => parent[' . $value?->getIndex() . ']');
                }
            } else {
                if ($key == 'landmark') {
                    Log  ::log('"' . $key . '" => [');
                    $this->displayScriptBlocks($value['_custom'] ?? []);
                    Log  ::log('],');
                } elseif (is_array($value)) {
                    Log  ::log('"' . $key . '" => [');
                    $this->displayScriptBlocks($value);
                    Log  ::log('],');
                } else {
                    Log  ::log('"' . $key . '" => ' . json_encode($value, JSON_PRETTY_PRINT) . ',', replaceNewLine: false);
                }
            }
        }
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
                    if ($solve = $this->resolve($resolver, $i)) {
                        $this->restoreResolver($resolver, $solve['save']);
                        $resolver->setLandmark($solve['step']);
                        $this->resolveSettings($resolver);
                        $this->saveBlock($resolver);
                        $script = $resolver->getScript();
                        $i = $script[sizeof($script) - 1]->getEnd();
                        $this->clearObjectify($resolver);
                    }
                } catch (Exception $e) {
                    if ($e->getMessage() !== self::SKIP) {
                        throw $e;
                    }
                    $this->clearObjectify($resolver);
                }
            }
        } catch (Exception $e) {
            if ($e->getMessage() !== self::FINISH && $e->getMessage() !== self::END_OF_FILE) {
                throw $e;
            }
        }

        if ($resolver->getI() === $resolver->getContent()->getLength() - 1) {
            $parent = $resolver->getParent();
            $last = $parent->getLastChild();
            if (!$last) {
                $this->addMissedEnd($resolver, 0, $resolver->getContent()->getLength());
            } elseif ($last?->getEnd() + 1 < $resolver->getContent()->getLength()) {
                $start = $last->getEnd() + 1;
                $this->addMissedEnd($resolver, $start, $resolver->getContent()->getLength());
            }
        }

        return [$resolver->getScript(), $resolver->getI()];
    }

    public function addMissedEnd(LandmarkResolverModel $resolver, int $start, int $end): void
    {
        $last = $resolver->getParent()->getLastChild();
        $data = $this->getMissedData($resolver, $start, $end);
        if (empty($data['missed'])) {
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
        $missed = $resolver->getContent()->iSubStr($start, $end);
        $prepare = $this->schema['prepare']['missed'] ?? null;
        if ($prepare && is_callable($prepare)) {
            $missed = $prepare($missed);
        }

        return [
            "missed" => $missed
        ];
    }

    private function resolve(LandmarkResolverModel $resolver, int &$i)
    {
        // @TODO remove this, and think of better fail save
        $this->iterations++;
        // if ($this->iterations > 2500) {
        //     throw new \Error('Infinite loop');
        // }
        // Log ::increaseIndent();

        $i = $this->handleComment($resolver, $i);

        $content = $resolver->getContent();
        if (is_null($content->getLetter($i))) {
            /* Don't end file with exception but let it slowly get out of foreach */
            // $resolver->i--;
            // throw new Exception(self::END_OF_FILE);
            // Log ::decreaseIndent();
            return false;
        }

        // Don't skip string - $resolver->setI(Str::skip($content->getLetter($i), $i, $content));
        $resolver->setI($i);
        $resolver->setLetter($content->getLetter($i));
        // Log ::log($i . ' Letter: `' . $resolver->getLetter() . '`, `' . $resolver->getLmStart() . '`, ' . $resolver->getContent()->getLength() . ', possible: ' . implode(', ', array_keys($resolver->getLandmark())));
        if (isset($resolver->getLandmark()[$resolver->getLetter()])) {
            $solve = $this->resolveStringLandmark($resolver);
            if ($solve) {
                $i = $solve['save']['i'];
                // Log ::decreaseIndent();
                return $solve;
            }
        }

        if (isset($resolver->getLandmark()['_m']) && ($solve = $this->resolveMethodLandmark($resolver))) {
            $i = $solve['save']['i'];
            // Log ::decreaseIndent();
            return $solve;
        }

        /* @DOUBLE_CHECK this operation might be unnecessary, currently I can't think of example where this helps but it is quite late at night */
        // If nothing was found but we have descended some steps (more then one) into the map, try with the same letter from the start
        if ($this->isInLandmark($resolver->getLmStart())) {
            $i--;
            $resolver->setLetter($content->getLetter($i));
        }
        // Log ::log("Nothign was found!");
        $this->clearObjectify($resolver);

        // Log ::decreaseIndent();
        return false;
    }

    public function isInLandmark(?int $lmStart): bool
    {
        // @TODO Find better solution for this validate as debug with path > 1 does seem wrong
        return !is_null($lmStart) && sizeof($this->debug['path']) > 1;
    }

    public function handleComment(LandmarkResolverModel|CustomMethodEssentialsModel $resolver, int $start): int
    {
        if (!$this->schema['remove']['comments'] && $this->retrieveComments) {
            $this->tryToRetrieveComment($resolver, $start);
            return $start;
        }

        if (!$this->schema['remove']['comments'] && !$this->retrieveComments) {
            return $this->skipComment($resolver->getContent(), $start);
        }
    }

    public function tryToRetrieveComment(
        LandmarkResolverModel|CustomMethodEssentialsModel $resolver,
        int $start, ?int $current = null, ?array $landmarks = null
    ): void {
        $content = $resolver->getContent();
        $lmStart = $resolver->getLmStart();
        if (is_null($landmarks)) {
            $landmarks = $this->schema['comments'];
        }

        if (is_null($current)) {
            $current = $start;
        }

        $letter = $content->getLetter($current);
        $landmark = $landmarks[$letter] ?? null;

        if (is_null($landmark)) {
            return;
        }

        if (is_array($landmark)) {
            $this->tryToRetrieveComment($resolver, $start, $current + 1, $landmark);
            return;
        }

        $end = $content->find($landmark, $current + 1);
        if ($end === false) {
            $end = $content->getLength();
        }

        $comment = $content->iSubStr($start, $end);
        // Save comment to later add it to the next blok
        $this->comments[] = [
            "content" => $comment,
            "start" => $start,
            "end" => $end,
        ];

        // Replace comment with whitespace (except new line)
        $comment = preg_replace('/[^\n]/', ' ', $comment);
        $content->iSplice($start, $end, str_split($comment));
        if (!$this->isInLandmark($lmStart)) {
            list($letter, $pos) = Str::getPreviousLetter($start - 1, $content);
            $betweenComment = $content->iSubStr($pos, $start - 1);
            // If it's in the same line, try to attach to the last block
            if (strpos($betweenComment, "\n") === false) {
                if ($resolver->getParent() instanceof ScriptBlockModel) {
                    // This mean we are at the top of the file
                    return;
                }

                if ($resolver instanceof CustomMethodEssentialsModel) {
                    $block = $resolver->getPrevious();
                } else {
                    $block = $resolver->getParent()->getLastChild();
                }

                if ($block) {
                    $block->setComments(array_merge($block->getComments(), $this->comments));
                }
                $this->comments = [];
            }
        }
    }

    public function skipComment(Content $content, int $start, ?int $current = null, ?array $landmarks = null): int
    {
        if (is_null($landmarks)) {
            $landmarks = $this->schema['comments'];
        }

        if (is_null($current)) {
            $current = $start;
        }

        $letter = $content->getLetter($current);
        $landmark = $landmarks[$letter] ?? null;

        if (is_null($landmark)) {
            return $start;
        }

        if (is_array($landmark)) {
            return $this->skipComment($content, $start, $current + 1, $landmark);
        }

        $end = $content->find($landmark, $current + 1);
        if ($end === false) {
            return $content->getLength();
        }
        return $end;
    }

    private function clearObjectify(LandmarkResolverModel $resolver)
    {
        $resolver->setLandmark($resolver->getMap());
        $resolver->setData([]);
        $resolver->setLmStart(null);
        $this->debug["path"] = [];
    }

    public function resolveStringLandmark(LandmarkResolverModel $resolver): bool | array
    {
        $this->debug["path"][] = $resolver->getLetter();
        $possibleLandmark = $resolver->getLandmark()[$resolver->getLetter()];

        // Log ::log('New string lm, oprions: ' . implode(', ', array_keys($possibleLandmark)));
        if (is_null($resolver->getLmStart())) {
            $resolver->setLmStart($resolver->getI());
        }

        $solve = false;
        if (isset($possibleLandmark['_stop'])) {
            $solve = [
                "step" => $possibleLandmark,
                "save" => $this->saveResolver($resolver),
                "end"  => $resolver->getI()
            ];
        }

        $res = $this->tryToFindNextMatch($resolver, $possibleLandmark);
        if ($res) {
            return $res;
        }

        return $solve;
    }

    public function getMethod(string $methodName): array
    {
        $method = $this->methods[$methodName]
            ?? throw new Exception("Method " . htmlentities($methodName) . " not found", 404);

        $callable = $this->schema['methods'][$method['name']]
            ?? throw new Exception("Method " . htmlentities($method['name']) . " not defined", 400);

        is_callable($callable)
            or throw new Exception("Method " . htmlentities($method['name']) . " is not callable", 400);

        return [$method, $callable];
    }

    public function resolveMethodLandmark(LandmarkResolverModel $resolver): bool | array
    {
        $preSave = $this->saveResolver($resolver);
        $solve = null;
        // Log ::log('Method to check: ' . implode(', ', array_keys($resolver->getLandmark()['_m'])));
        foreach ($resolver->getLandmark()['_m'] as $methodName => $step) {
            list($method, $callable) = $this->getMethod($methodName);
            // @TODO figure out if it's not better to pass Resolver and just restore save state after failure
            // Set essentials
            $essentialsValues = [
                "content"  => $resolver->getContent(),
                "lmStart"  => $resolver->getLmStart(),
                "letter"   => $resolver->getLetter(),
                "i"        => $resolver->getI(),
                "data"     => $resolver->getData(),
                "previous" => $resolver->getParent()?->getLastChild(),
                "methods"  => $this->schema['methods'],
                "reader"   => $this,
            ];
            $essentials = new CustomMethodEssentialsModel();
            $essentials->set($essentialsValues);

            // Call method
            $res = $callable($essentials, ...$method['params']);

            if (!$res) {
                continue;
            }

            $this->debug["path"][] = $methodName;
            $debug = $this->debug["path"];

            $save = $this->saveResolver($resolver);
            $this->resolveThen($resolver, $essentials, $method);

            if (is_null($resolver->getLmStart())) {
                $resolver->setLmStart($resolver->getI());
            }

            // Update changed essentials
            $this->updateFromEssentials($resolver, $essentials);

            // Log ::log('New method `' . $methodName . '` lm, oprions: ' . implode(', ', array_keys($step)));

            $res = $this->tryToFindNextMatch($resolver, $step);
            if (
                !$res
                && isset($step['_stop'])
                && (
                    !isset($solve['end'])
                    || !isset($res['end'])
                    || $res['end'] > $solve['end']
                )
            ) {
                $solve = [
                    "step" => $step,
                    "save" => $this->saveResolver($resolver),
                    "end" => $resolver->getI()
                ];
            } elseif (
                $res
                && (
                    !isset($solve['end'])
                    || $res['end'] > $solve['end']
                )
            ) {
                $solve = $res;
            }
            $this->restoreResolver($resolver, $save);
        }

        return $solve ?? false;
    }

    public function updateFromEssentials(LandmarkResolverModel $resolver, CustomMethodEssentialsModel $essentials): void
    {
        $skipReplace = [
            "methods"  => true,
            "previous" => true,
            "lmStart"  => true,
            "reader"   => true,
        ];

        foreach ($essentials as $key => $value) {
            if ($skipReplace[$key] ?? false) {
                continue;
            }
            $getter = 'get' . Str::pascalize($key);
            $setter = 'set' . Str::pascalize($key);
            $resolver->$setter($essentials->$getter());
        }
    }

    public function resolveThen(LandmarkResolverModel $resolver, CustomMethodEssentialsModel $essentials, array $method): void
    {
        if (!isset($method["then"])) {
            return;
        }

        list($method, $callable) = $this->getMethod($method['then']);
        $callable($essentials, ...$method['params']);
        $this->updateFromEssentials($resolver, $essentials);

        $this->resolveThen($resolver, $essentials, $method);
    }

    public function tryToFindNextMatch(LandmarkResolverModel $resolver, array $posLandmark): bool | array
    {
        $save = $this->saveResolver($resolver, []);
        $resolver->setLandmark($posLandmark);
        $resolver->i++;
        $res = $this->resolve($resolver, $resolver->i);
        if ($res) {
            return $res;
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
        // Log ::log('Save block - ' . json_encode($resolver->getLandmark()['_custom'] ?? []) . ", debug: " . implode(' => ', $this->debug['path']));

        $item = new BlockModel(
            start: $resolver->getLmStart(),
            end: $resolver->getI(),
            landmark: $resolver->getLandmark(),
            data: $resolver->getData(),
            index: \sizeof($resolver->getScript()),
            parent: $resolver->getParent(),
            path: $this->debug['path'],
            comments: $this->comments
        );
        $this->comments = [];

        $block = $item->getLandmark()["_block"] ?? false;

        if ($block) {
            $item->setBlockStart($item->getEnd() + 1);
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

        $this->addMissed($resolver, $resolver->getParent());

        // Exception if no child was found
        if ($block && sizeof($item->getChildren()) === 0) {
            $start = $item->getBlockStart();
            $end = $item->getBlockEnd();

            if ($end < $start) {
                return;
            }

            $inside = $resolver->getContent()->iSubStr($start, $end);
            $data = $this->getMissedData($resolver, $start, $end);

            if (strlen($data["missed"]) != 0) {
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
            if (empty($data["missed"])) {
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
        $this->retrieveComments = false;
        list($endBlocks, $i) = $this->objectify($content, $blockSet['map'], $start, $parent);
        $this->retrieveComments = true;

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
            $end = $endBlock->getEnd();
        } else {
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
        $prepare = $this->schema['prepare']['content'] ?? null;
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
            $content->splice($start, null);
        } else {
            $content->splice($start, $end + 1 - $start);
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
                    array_splice($instrKeys, $i + 1, 0, $instr . $subInstr);
                }

                unset($instructions[$instr]["_extend"]);

                if (empty($instructions[$instr])) {
                    unset($instructions[$instr]);
                    array_splice($instrKeys, $i, 1);
                    $i--;
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
                        $nameContent = new Content($name);
                        $strLen = Str::skip($name[0], 0, $nameContent);
                        if ($strLen !== 3 && $strLen !== 4) {
                            throw new Exception("OR literal method can be only made from 1 letter at time (optionaly with negation `!`)" . $item['name'], 400);
                        }
                        $methodName = trim($nameContent->subStr(0, $strLen), $name[0]);
                        if (!isset($item['_skip']) || !$item['_skip']) {
                            $methods[$name] = $nextStep;
                        }
                        $this->methods[$name] = $item;
                        if ($name[1] === '!') {
                            $this->schema['methods'][$methodName] = ClosureFactory::generateReversalClosure($name[2]);
                        } else {
                            $this->schema['methods'][$methodName] = ClosureFactory::generateEqualClosure($name[1]);
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
