<?php declare(strict_types=1);

namespace Tetraquark;

use Orator\Log;
use Content\Utf8 as Content;
use Tetraquark\Model\{
    BasePolymorphicModel,
    CustomMethodEssentialsModel,
    LandmarkResolverModel,
    Block\BlockModel,
    Block\ScriptBlockModel,
    SettingsModel
};
use Tetraquark\Contract\{BlockModelInterface, AnalyzerInterface};
use Tetraquark\Factory\ClosureFactory;
use Tetraquark\Trait\ReaderDisplayTrait;

/**
 *  Class for reading script and seperating it into managable blocks
 */
class Reader
{
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
    protected bool $failsave = true;
    protected int $recursionLimit = 2500;

    public const FLAG_SKIP = '_skip';
    public const FLAG_FINISH = '_finish';
    public const SKIP = 'skip';
    public const FINISH = 'finish';
    public const END_OF_FILE = 'end of file';
    public const EMPTY_METHOD = 'e';
    public const EMPTY = '_empty';

    use ReaderDisplayTrait;

    public function __construct(
        ?string $analyzerClass = null,
        protected bool $cache = true,
    ) {
        if (is_null($analyzerClass)) {
            return;
        }

        $this->compile($analyzerClass);
    }

    public function compile(?string $analyzer): void
    {
        if (!class_exists($analyzer)) {
            throw new Exception(sprintf("Class %s doesn't exists, can't compile settings", $analyzer), 500);
        }
        $implemented = class_implements($analyzer);
        if (!isset($implemented[AnalyzerInterface::class])) {
            throw new Exception(sprintf("Class %s is not implementing Analyzer interface", $analyzer), 500);
        }

        $this->schema = $this->schemaSetDefaults($analyzer::getSchema(), $this->getSchemaDefaults());
        $this->name   = $analyzer::getName();
        $compiled     = self::$compiled[$this->name] ?? null;

        if ($this->cache && isset($compiled)) {
            $this->map     = $compiled['map'];
            $this->methods = $compiled['methods'];
        } else {
            $this->map = $this->generateBlocksMap();
            self::$compiled[$this->name] = [
                "map" => $this->map,
                "methods" => $this->methods,
            ];
        }

        // die(json_encode($this->methods, JSON_PRETTY_PRINT));
        // die(json_encode($this->map, JSON_PRETTY_PRINT));
    }

    /**
     * @codeCoverageIgnore
     */
    public function getMap(): array
    {
        return $this->map;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getSchema(): array
    {
        return $this->schema;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getPath(): array
    {
        return $this->path;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getSchemaDefaults(): array
    {
        return [
            "shared" => [
                "ends" => []
            ],
            "comments" => [],
            "remove" => [
                "comments" => false,
                "additional" => false,
            ],
            "instructions" => [],
            "methods" => $this->getDefaultMethods(),
            "prepare" => [
                "content" => null,
                "missed" => null
            ],
        ];
    }

    public function setFailsave(bool $failsave): self
    {
        $this->failsave = $failsave;
        return $this;
    }

    public function getFailsave(): bool
    {
        return $this->failsave;
    }

    public function setRecursionLimit(int $recursionLimit): self
    {
        $this->recursionLimit = $recursionLimit;
        return $this;
    }

    public function getRecursionLimit(): int
    {
        return $this->recursionLimit;
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
        // @codeCoverageIgnoreStart
        if (is_null($short)) {
            $short = $displayBlocks;
        }
        // @codeCoverageIgnoreEnd

        if ($isPath) {
            if (!is_file($script)) {
                throw new Exception("Passed file was not found", 404);
            }

            $script = file_get_contents($script);
        }

        // @codeCoverageIgnoreStart
        if ($displayBlocks || $short) {
            Log  ::timerStart();
        }
        // @codeCoverageIgnoreEnd

        $content = $this->removeCommentsAndAdditional(new Content($script));
        $content = $this->customPrepare($content);

        // @codeCoverageIgnoreStart
        if ($displayBlocks) {
            Log  ::log($content . '');
        }
        // @codeCoverageIgnoreEnd

        $script = new ScriptBlockModel();
        list($script, $end) = $this->objectify($content, $this->map, parent: $script);

        // @codeCoverageIgnoreStart
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
        // @codeCoverageIgnoreEnd

        return $script;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setRerieveComments(bool $retrieveComments): self
    {
        $this->retrieveComments = $retrieveComments;
        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getRerieveComments(): bool
    {
        return $this->retrieveComments;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setSaveRerievedComments(bool $setRetrievedComments): self
    {
        $this->setRetrievedComments = $setRetrievedComments;
        return $this;
    }

    /**
     * @codeCoverageIgnore
     */function getSaveRerievedComments(): bool
    {
        return $this->setRetrievedComments;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setComments(array $comments): self
    {
        $this->comments = $comments;
        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getComments(): array
    {
        return $this->comments;
    }

    public function objectify(Content $content, array $map, int $start = 0, ?BlockModelInterface $parent = null): array
    {
        $failsave = new \stdClass();
        $failsave->counter = 0;
        $failsave->limit = $this->getRecursionLimit();
        $failsave->stop = $this->getFailsave();
        $resolver = new LandmarkResolverModel([
            "letter"     => null,
            "landmark"   => $map,
            "lmStart"    => null,
            "script"     => [],
            "i"          => $start,
            "content"    => $content,
            "data"       => [],
            "settings"   => new SettingsModel(),
            "map"        => $map,
            "parent"     => $parent,
            "failsave"   => clone $failsave,
        ]);

        try {
            for ($i=$start; $i < $content->getLength(); $i++) {
                try {
                    // Log::log('I: ' . $i . ", Letter: " . $resolver->getContent()->getLetter(
                    //     $i
                    // ));
                    if ($solve = $this->resolve($resolver, $i)) {
                        $this->restoreResolver($resolver, $solve['save']);
                        $resolver->setLandmark($solve['step']);
                        $this->resolveSettings($resolver);
                        $this->saveBlock($resolver);
                        $script = $resolver->getScript();
                        $item = $script[sizeof($script) - 1];
                        $i = $item->getEnd();
                        $this->clearObjectify($resolver);

                        // Some variables normally share their end/start:
                        // `let a = 'a'\nlet b = 'd'` (variable a is sharing its end (`\n`) with variable b)
                        // `let a = 'a';let b = 'd'` (variable a is sharing its end (`;`) with variable b)
                        // or `if(true){}var a = 'b'` (`if` is shraing its end (`}`) wth variable a)
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
                            $i--;
                        }

                        $resolver->setFailsave(clone $failsave);
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

    public function resolve(LandmarkResolverModel $resolver, int &$i)
    {
        $failsave = $resolver->getFailsave();
        $failsave->counter++;
        if ($failsave->stop && $failsave->limit < $failsave->counter) {
            throw new \Error('Infinite loop', 500);
        }
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
        // if ($this->isInLandmark($resolver->getLmStart())) {
        //     $i--;
        //     $resolver->setLetter($content->getLetter($i));
        // }
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

        return $start;
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
                if ($resolver instanceof CustomMethodEssentialsModel) {
                    $block = $resolver->getPrevious();
                } else {
                    $block = $resolver->getParent()->getLastChild();
                }

                if (!$block) {
                    return;
                }

                $block->setComments(array_merge($block->getComments(), $this->comments));
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

    public function clearObjectify(LandmarkResolverModel $resolver)
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
        if (isset($resolver->getLandmark()[self::FLAG_SKIP])) {
            $resolver->getSettings()->increaseSkip();
            throw new Exception(self::SKIP);
        }

        if ($resolver->getSettings()->getSkip() > 0) {
            $resolver->getSettings()->decreaseSkip();
            throw new Exception(self::SKIP);
        }

        if (isset($resolver->getLandmark()[self::FLAG_FINISH])) {
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

    // @TODO Move to LandmarkResolverModel
    public function saveResolver(LandmarkResolverModel $resolver, array $remove = []): array
    {
        $remove = array_merge($remove, ['recursion']);
        $save = $resolver->toArray();
        foreach ($remove as $value) {
            unset($save[$value]);
        }
        return $save;
    }

    // @TODO Move to LandmarkResolverModel
    public function restoreResolver(LandmarkResolverModel $resolver, array $save): void
    {
        $resolver->set($save);
    }

    public function saveBlock(LandmarkResolverModel $resolver): void
    {
        // Log ::log('Save block - ' . json_encode($resolver->getLandmark()['_custom'] ?? []) . ", debug: " . implode(' => ', $this->debug['path']));

        $landmark = ($resolver->getLandmark()['_missed'] ?? false) ? $resolver->getLandmark() : ($resolver->getLandmark()['_custom'] ?? []);
        $block = $resolver->getLandmark()["_block"] ?? false;
        $item = new BlockModel(
            start: $resolver->getLmStart(),
            end: $resolver->getI(),
            landmark: $landmark,
            data: $resolver->getData(),
            isBlock: ($block === false ? false : true),
            index: \sizeof($resolver->getScript()),
            parent: $resolver->getParent(),
            comments: $this->comments
        );
        $this->comments = [];


        if ($block) {
            $item->setBlockStart($item->getEnd() + 1);
            list($i, $blocks) = $this->findBlocksEnd($block, $resolver->getContent(), $item->getEnd() + 1, $item);
            $resolver->setI($i);
            $item->setEnd($i);
            $item->setChildren($blocks);
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
                    isBlock: false,
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
                isBlock: false,
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
            if (!Content::isWhitespace($newContent->getLetter(0))) {
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
                return $i + $needleSize - 1;
            }
        }
        return false;
    }

    public function generateBlocksMap(): array
    {
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
        // @DEPRICATED
        // if (isset($block['_expand'])) {
        //     foreach ($block['_expand'] as $key => $subBlock) {
        //         $block['_expand'][$key] = $this->encloseCustomData($subBlock);
        //     }
        // }
        return $block;
    }

    public function mergeMaps(array $maps, array $merged = []): array
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

    public function createMapItem(string|array $item, string $type): ?array
    {
        if (empty($item)) {
            return null;
        }
        return ["item" => $item, "type" => $type];
    }

    public function createMethodMapItems(string $currentItem, bool $skip = false): array
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

    public function getDefaultMethods(): array
    {
        return [
            "s" => function (CustomMethodEssentialsModel $essentials): bool
            {
                $letter = $essentials->getLetter();
                if (!Content::isWhitespace($letter ?? '')) {
                    return false;
                }
                list($letter, $pos) = Str::getNextLetter($essentials->getI(), $essentials->getContent());
                if (strlen($letter) !== 0) {
                    $essentials->setI($pos - 1);
                }
                return true;
            },
            "find" => function (CustomMethodEssentialsModel $essentials, string|array $needle, null|array|string $hayStarter = null, ?string $name = null): bool
            {
                $content = $essentials->getContent();
                $index   = $essentials->getI();
                $data    = $essentials->getData();

                list($pos, $foundkey) = Str::skipBlock($needle, $index, $content, $hayStarter);
                $pos--;

                if (is_null($foundkey)) {
                    return false;
                }

                $data["foundkey"] = $foundkey;

                if (!is_null($name)) {
                    $data[$name] = trim($content->iSubStr($index, $pos - \mb_strlen($foundkey)));
                }

                $letter = $content->getLetter($pos);

                $essentials->setLetter($letter);
                $essentials->setI($pos);
                $essentials->setData($data);

                return true;
            },
        ];
    }
}
