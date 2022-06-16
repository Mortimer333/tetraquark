<?php declare(strict_types=1);

namespace Tetraquark\Foundation;

use \Tetraquark\Trait\{BlockGetSetTrait, BlockMapsTrait, BlockAliasMapTrait};
use \Tetraquark\Foundation\{
    CommentBlockAbstract as CommentBlock,
    ConditionBlockAbstract as ConditionBlock,
    MethodBlockAbstract as MethodBlock,
    VariableBlockAbstract as VariableBlock
};
use \Tetraquark\Contract\{Block as BlockInterface};
use \Tetraquark\{Exception, Block, Log, Validate, Str, Content, Folder, Import};

abstract class BlockAbstract
{
    public const DEFAULT_MODE = 'objectify';
    // I've seperated related functionality to Traits to make this file more managable
    use BlockGetSetTrait;   // Holds all get and set functions
    use BlockMapsTrait;     // Has $blocksMap, $classBlocksMap, $objectBlocksMap, $callerBlocksMap and $arrayBlocksMap variables
    use BlockAliasMapTrait; // Contains our alias creation map
    static protected Block\ScriptBlock $mainScript;
    static protected Content $content;
    static protected Scope   $globalScope;
    static protected Folder  $folder;
    static protected Import  $import;
    static protected array   $mappedAliases = [];
    static protected array   $settings = [
        "single_file" => true
    ];

    protected int    $caret = 0;
    protected bool   $endFunction = false;

    /** @var Content $instruction Actual block representation in code */
    protected Content $instruction;

    protected int    $instructionStart;
    protected string $name    = '';
    protected string $subtype = '';
    protected string $mode    = '';

    /** @var int Queue indicator */
    protected int    $childIndex;

    /** @var BlockAbstract Parent of this block */
    // protected BlockAbstract $parent;

    protected array  $aliasesMap = [];

    /** @var BlockAbstract[] $blocks Array of Blocks */
    protected array  $blocks = [];

    protected array  $endChars = [
        "\n" => true,
        ";" => true,
    ];

    /** @var int Amount of opened brackets */
    protected int $bracketsCount = 0;

    /** @var string Holds name of method which will return array of items this Block is saved in */
    protected string $placementMethod = "getBlocks";

    public function __construct(
        int $start = 0,
        string $subtype = '',
        protected ?BlockInterface $parent = null,
    ) {
        if (!isset(self::$folder)) {
            self::$folder = new Folder();
        }
        if (!isset(self::$import)) {
            self::$import = new Import();
        }
        $this->setMode(self::DEFAULT_MODE);
        $this->setSubtype($subtype);
        $this->objectify($start);
    }

    public function aliasExists(string $name): bool
    {
        return (bool) (self::$mappedAliases[$name] ?? false);
    }

    protected function journeyForBlockClassName(string $name, string &$mappedWord, string &$possibleUndefined, int &$i, ?array $blocksMap = null): string | array | null
    {
        if (\is_null($blocksMap)) {
            $blocksMap = $this->getDefaultMap();
        }

        if (isset($blocksMap[$name])) {
            if ($blocksMap[$name] === false) {
                $mappedWord = '';
                return null;
            }
            return $this->checkMapResult($blocksMap[$name], $i);
        }

        if (isset($blocksMap['default'])) {
            $mappedWord = \mb_substr($mappedWord, 0, -1);
            $possibleUndefined = \mb_substr($possibleUndefined, 0, -1);
            $i--;
            return $this->checkMapResult($blocksMap['default'], $i);
        }

        // try to start new path with this letter
        $blocksMap = $this->getDefaultMap();
        $mappedWord = $name;
        return $this->checkMapResult($blocksMap[$name] ?? null, $i);
    }

    protected function checkMapResult(array|string|null $nextStep, int $i): array|string|null
    {
        if (\is_string($nextStep) && \method_exists($this, $nextStep)) {
            return $this->$nextStep($i);
        }
        return $nextStep;
    }

    protected function blockFactory(string $hint, string $className, int $start, string &$possibleUndefined, array &$blocks): BlockInterface
    {
        $class = self::blockExistsOrThrow($className);

        // If this is variable creatiion without any type declaration then its attribute assignment and we shouldn't add anything before it
        if ($hint == '=') {
            $hint = '';
        }

        // if ($class == Block\ChainLinkBlock::class) {
        //     if (
        //         $this::class !== Block\ChainLinkBlock::class
        //         && $this::class !== Block\BracketChainLinkBlock::class
        //         || (
        //             $this::class === Block\BracketChainLinkBlock::class
        //             && $this->getSubtype() === Block\BracketChainLinkBlock::BRACKET_BLOCK_CREATE
        //         )
        //     ) {
        //         $block = new $class($start, Block\ChainLinkBlock::FIRST, $this);
        //
        //         $possibleUndefined = \mb_substr($possibleUndefined, 0, -($block->getInstruction()->getLength() + 1));
        //         if (Validate::isValidUndefined($possibleUndefined)) {
        //             $undefinedBlock = new Block\UndefinedBlock($start - \mb_strlen($possibleUndefined), $possibleUndefined, '', $this);
        //             $undefinedBlock->setChildIndex(\sizeof($blocks));
        //             $blocks[] = $undefinedBlock;
        //         }
        //
        //         $possibleUndefined = '';
        //         return $block;
        //     }
        //     return new $class($start + 1, $hint, $this, '.');
        // }
        return new $class($start, $hint, $this);
    }

    protected function findInstructionEnd(int $start, string $name = '', ?array $endChars = null, bool $skipString = true): void
    {
        if (\is_null($endChars)) {
            $endChars = $this->endChars;
        }

        $properEnd = null;
        for ($i=$start; $i < self::$content->getLength(); $i++) {
            $letter = self::$content->getLetter($i);

            if ($endChars[$letter] ?? false) {
                $properEnd = $i;
                $this->setCaret($properEnd);
                break;
            }

            list($letter, $i) = $this->skipIfNeccessary(self::$content, $letter, $i);

            if ($endChars[$letter] ?? false) {
                $properEnd = $i;
                $this->setCaret($properEnd);
                break;
            }
        }

        if (is_null($properEnd)) {
            $properEnd = self::$content->getLength();
            $this->setCaret($properEnd);
        }

        $properStart = $start - \mb_strlen($name);
        $this->setInstructionStart($properStart)
            ->setInstruction(self::$content->iCutToContent($properStart, $properEnd - 1));
    }

    protected function findInstructionStart(int $end, ?array $endChars = null): void
    {
        if (\is_null($endChars)) {
            $endChars = $this->endChars;
        }

        $properStart = null;
        for ($i=$end - 1; $i >= 0; $i--) {
            $letter = self::$content->getLetter($i);
            if (
                ($startsTemplate = Validate::isTemplateLiteralLandmark($letter, ''))
                || Validate::isStringLandmark($letter, '')
            ) {
                $i = $this->skipString($letter, $i - 1, self::$content, $startsTemplate, true);
                $letter = self::$content->getLetter($i);
            }

            if ($endChars[$letter] ?? false) {
                $properStart = $i + 1;
                break;
            }
        }

        if (is_null($properStart)) {
            $properStart = 0;
        }

        $this->setInstructionStart($properStart)
            ->setInstruction(self::$content->iCutToContent($properStart, $end - 1));
    }

    protected function constructBlock(string $mappedWord, string $className, int &$i, string &$possibleUndefined, array &$blocks): ?BlockAbstract
    {
        $block = $this->blockFactory($mappedWord, $className, $i, $possibleUndefined, $blocks);
        $i = $block->getCaret();
        return $block;
    }

    protected function generateUndefined(int $start, string $possibleUndefined, int $childIndex): BlockInterface
    {
        Log::log('gen und: ' . $possibleUndefined);
        if ($this::class === Block\ClassBlock::class) {
            $undefined = new Block\EmptyAttributeBlock($start, $possibleUndefined, '', $this);
        } elseif ($this::class === Block\ImportBlock::class) {
            $undefined = new Block\ImportAsBlock($start, $possibleUndefined, '', $this);
        } elseif ($this::class === Block\ExportBlock::class) {
            $undefined = new Block\ExportAsBlock($start, $possibleUndefined, '', $this);
        } else {
            $undefined = new Block\UndefinedBlock($start, $possibleUndefined, '', $this);
        }
        $undefined->setChildIndex($childIndex);
        return $undefined;
    }

    protected function canHaveAnotherChild(string $map): bool
    {
        return (
            $this::class === Block\ChainLinkBlock::class
            || $this::class === Block\BracketChainLinkBlock::class
        ) && $map !== 'ChainLinkBlock'
        && $map !== 'BracketChainLinkBlock';
    }

    protected function createSubBlocks(?int $start = null, bool $onlyOne = false, $special = false): array
    {
        if (is_null($start)) {
            $start = $this->getCaret();
        }

        $map = $this->getDefaultMap()[' '] ?? null;
        $mappedWord = '';
        $possibleUndefined = '';
        $undefinedEnds = ["\n" => true, ";" => true];
        $blocks = [];
        Log::increaseIndent();
        for ($i=$start; $i < self::$content->getLength(); $i++) {
            $letter = self::$content->getLetter($i);
            // Log::log($this::class . " | Letter: " . $letter . ', i: ' . $i . ', ' . self::$content->getLength() . ', ["' . implode('", "', array_keys($this->endChars)) . '"]' );
            if (
                ($startsTemplate = Validate::isTemplateLiteralLandmark($letter, ''))
                || Validate::isStringLandmark($letter, '')
            ) {
                $oldPos = $i;
                $i = $this->skipString($letter, $i + 1, self::$content, $startsTemplate);

                if (Validate::isValidUndefined($possibleUndefined)) {
                    $blocks[] = $this->generateUndefined($oldPos - \mb_strlen($possibleUndefined), $possibleUndefined, \sizeof($blocks));
                }
                if (!$this instanceof Block\ObjectBlock) {
                    $string = new Block\StringBlock($oldPos, self::$content->iSubStr($oldPos, $i - 1), '', $this);
                    $string->setChildIndex(\sizeof($blocks));
                    $blocks[] = $string;
                }

                if (\is_null(self::$content->getLetter($i))) {
                    $possibleUndefined = '';
                    break;
                }

                $letter = self::$content->getLetter($i);
                $mappedWord = '';
                $possibleUndefined = '';
            }

            $mappedWord .= $letter;
            $possibleUndefined .= $letter;
            $map = $this->journeyForBlockClassName($letter, $mappedWord, $possibleUndefined, $i, $map);
            if (gettype($map) == 'string') {
                if ($this->canHaveAnotherChild($map) && $onlyOne) {
                    $i -= \mb_strlen($possibleUndefined);
                    $possibleUndefined = '';
                    break;
                }

                $oldPos = $i - \mb_strlen($possibleUndefined);
                $block = $this->constructBlock($mappedWord, $map, $i, $possibleUndefined, $blocks);
                $mappedWordLen = \mb_strlen($mappedWord);
                $instStart = $block->getInstructionStart();
                // Check if instruction did include semicolon
                list($nextLetter, $pos) = $this->getNextLetter($i + 1, self::$content);
                if ($nextLetter == ';') {
                    $i = $pos;
                }

                $lenOfUndefined = $instStart - $oldPos;
                if ($lenOfUndefined - 1 > 0) {
                    $possibleUndefined = \mb_substr($possibleUndefined, 0, $lenOfUndefined - 1);
                } else {
                    $possibleUndefined = '';
                }

                if (Validate::isValidUndefined($possibleUndefined)) {
                    if ($onlyOne) {
                        $i = $oldPos;
                        $possibleUndefined = '';
                        break;
                    }
                    $blocks[] = $this->generateUndefined($oldPos - \mb_strlen($possibleUndefined), $possibleUndefined, \sizeof($blocks));
                }

                $possibleUndefined = '';
                $block->setChildIndex(\sizeof($blocks));
                $blocks[] = $block;
                $mappedWord = '';
                $map = $this->getDefaultMap()[' '] ?? null;

                // BlockChain Blocks can only have one child if they don't close the chain
                if ($onlyOne) {
                    break;
                }
                continue;
            } elseif (\is_null($map)) {
                $mappedWord = '';
            }

            if ($this->endChars[$letter] ?? false) {
                $possibleUndefined = \mb_substr($possibleUndefined, 0, -1);
                if (Validate::isValidUndefined($possibleUndefined)) {
                    if ($this->canHaveAnotherChild('UndefinedBlock') && $onlyOne) {
                        $i -= \mb_strlen($possibleUndefined) + 1;
                    } else {
                        $blocks[] = $this->generateUndefined($i - \mb_strlen($possibleUndefined), $possibleUndefined, \sizeof($blocks));
                    }
                    $possibleUndefined = '';
                }
                break;
            }
        }
        Log::decreaseIndent();
        if ($i == self::$content->getLength()) {
            $i--;
        }

        if (Validate::isValidUndefined($possibleUndefined)) {
            if ($this->canHaveAnotherChild('UndefinedBlock') && $onlyOne) {
                $i -= \mb_strlen($possibleUndefined);
                $possibleUndefined = '';
            } else {
                $blocks[] = $this->generateUndefined($i - \mb_strlen($possibleUndefined), $possibleUndefined, \sizeof($blocks));
            }
        }
        $this->setCaret($i);
        return $blocks;
    }

    protected function findAndSetName(string $prefix, array $ends): void
    {
        $instr = $this->getInstruction();
        $start = \mb_strlen($prefix) - 1;
        if ($start < 0) {
            $start = 0;
        }

        for ($i=$start; $i < $instr->getLength(); $i++) {
            $letter = $instr->getLetter($i);
            if ($ends[$letter] ?? false) {
                $this->setName($instr->iSubStr($start, $i - 1));
                return;
            }
        }
        if ($this instanceof Block\FunctionBlock) {
            $this->setName('');
            return;
        }
        $this->setName($instr->subStr($start));
    }

    protected function generateAliases(string $lastAlias = ''): string
    {
        if (strlen($this->getName()) > 0 && !$this->aliasExists($this->getName()) && $this->canBeAliased($this->getName(), $this)) {
            $alias = $this->generateAlias($lastAlias);

            if (strlen($alias) > 0) {
                $this->setAlias($this->getName(), $alias);
                $lastAlias = $alias;
            }
        }

        // Firstly set aliases to all blocks on this level
        foreach ($this->blocks as $block) {
            if (strlen($block->getName()) > 0 && !$this->aliasExists($block->getName()) && $this->canBeAliased($block->getName(), $block)) {
                $alias = $this->generateAlias($lastAlias);

                if (strlen($alias) > 0) {
                    $block->setAlias($block->getName(), $alias);
                    $lastAlias = $alias;
                }
            }

        }

        if ($this instanceof MethodBlock && !($this instanceof Block\NewClassBlock)) {
            foreach ($this->getArguments() as $argument) {
                foreach ($argument as $block) {
                    if (strlen($block->getName()) > 0 && !$this->aliasExists($block->getName()) && $this->canBeAliased($block->getName(), $block)) {
                        $alias = $this->generateAlias($lastAlias);

                        if (strlen($alias) > 0) {
                            $block->setAlias($block->getName(), $alias);
                            $lastAlias = $alias;
                        }
                    }

                    foreach ($block->getBlocks() as $subBlock) {
                        $lastAlias = $subBlock->generateAliases($lastAlias);
                    }
                }
            }

        } elseif ($this instanceof ConditionBlock) {
            foreach ($this->getCondBlocks() as $block) {
                if (strlen($block->getName()) > 0 && !$this->aliasExists($block->getName()) && $this->canBeAliased($block->getName(), $block)) {
                    $alias = $this->generateAlias($lastAlias);

                    if (strlen($alias) > 0) {
                        $block->setAlias($block->getName(), $alias);
                        $lastAlias = $alias;
                    }
                }

                foreach ($block->getBlocks() as $subBlock) {
                    $lastAlias = $subBlock->generateAliases($lastAlias);
                }
            }

        }

        // Then to level below
        foreach ($this->blocks as $block) {
            $lastAlias = $block->generateAliases($lastAlias);
        }
        return $lastAlias;
    }

    public function generateAlias(string $lastAlias): string
    {
        if (\mb_strlen($lastAlias) != 0) {
            $lastLetter = \mb_substr($lastAlias, -1);
        } else {
            $lastLetter = 'df';
        }

        if ($newAliasSufix = $this->aliasMap[$lastLetter]) {
            $newAlias = \mb_substr($lastAlias ?? '', 0, \mb_strlen($lastAlias ?? '') - 1) . $newAliasSufix;
        } else {
            $newAlias = ($lastAlias ?? '') . $this->aliasMap['df'];
        }
        if (Validate::isValidVariable($newAlias)) {
            return $newAlias;
        }
        return $this->generateAlias($newAlias, $newAlias);
    }

    public static function generateAliasStatic(string $lastAlias): string
    {
        if (\mb_strlen($lastAlias) != 0) {
            $lastLetter = \mb_substr($lastAlias, -1);
        } else {
            $lastLetter = 'df';
        }

        if ($newAliasSufix = self::$aliasMap[$lastLetter]) {
            $newAlias = \mb_substr($lastAlias ?? '', 0, \mb_strlen($lastAlias ?? '') - 1) . $newAliasSufix;
        } else {
            $newAlias = ($lastAlias ?? '') . $this->aliasMap['df'];
        }
        if (Validate::isValidVariable($newAlias)) {
            return $newAlias;
        }
        return self::generateAliasStatic($newAlias, $newAlias);
    }

    protected function replaceVariablesWithAliases(Content $content): string
    {
        $word = '';
        $minifiedValue = '';
        $stringInProgress = false;
        $templateVarInProgress = false;
        $templateLiteralInProgress = false;
        for ($i=0; $i < $content->getLength(); $i++) {
            $letter = $content->getLetter($i);
            $isLiteralLandmark = Validate::isTemplateLiteralLandmark($letter, $content->getLetter($i - 1) ?? '', $templateLiteralInProgress);
            if ($templateVarInProgress && !$isLiteralLandmark) {
                if ($letter == '}') {
                    $templateVarInProgress = false;
                    $alias = $this->getAlias($word);
                    $minifiedValue .= $alias . $letter;
                    $word = '';
                    continue;
                } else {
                    $word .= $letter;
                }
                continue;
            }

            if ($isLiteralLandmark) {
                $templateLiteralInProgress = !$templateLiteralInProgress;
            } elseif (Validate::isStringLandmark($letter, $content->getLetter($i - 1) ?? '')) {
                $oldPos = $i;
                $i = $this->skipString($letter, $i + 1, $content, false);
                $minifiedValue .= $letter . $word . $content->subStr($oldPos + 1, $i - $oldPos - 1);
                if (\is_null($content->getLetter($i))) {
                    break;
                }
                $word = '';
                $letter = $content->getLetter($i);
            }

            if (
                $templateLiteralInProgress
                && $this->startsTemplateLiteralVariable($letter, $content, $i)
            ) {
                $templateVarInProgress = true;
            }

            if (Validate::isSpecial($letter)) {
                $alias = $this->getAlias($word);
                $minifiedValue .= $alias . $letter;
                $word = '';
                continue;
            }

            $word .= $letter;
        }

        $alias = $this->getAlias($word);
        return $minifiedValue . $alias;
    }

    protected function startsTemplateLiteralVariable(string $letter, Content $value, int $i): bool
    {
        return ($value->getLetter($i - 1) ?? '') . $letter == '${'
            && ($value->getLetter($i - 2) ?? '') . ($value->getLetter($i - 1) ?? '') . $letter != '\${';
    }

    public function skipString(string $strLandmark, int $start, Content $content, bool $isTemplate = false, bool $reverse = false): int
    {
        $modifier = (((int)!$reverse) * 2) - 1;
        for ($i=$start; (!$reverse && $i < $content->getLength()) || ($reverse && $i >= 0); $i += $modifier) {
            $letter = $content->getLetter($i);
            if (
                $isTemplate
                && Validate::isTemplateLiteralLandmark($letter, $content->getLetter($i - 1) ?? '', true)
                && $letter === $strLandmark
            ) {
                return $i + $modifier;
            } elseif (
                !$isTemplate
                && Validate::isStringLandmark($letter, $content->getLetter($i - 1) ?? '', true)
                && $letter === $strLandmark
            ) {
                return $i + $modifier;
            }
        }
        return $i;
    }

    protected function fixScript(Content $instruction): string
    {
        $properInstr = '';
        for ($i=0; $i < $instruction->getLength(); $i++) {
            $letter = $instruction->getLetter($i);
            if (
                ($startsTemplate = Validate::isTemplateLiteralLandmark($letter, ''))
                || Validate::isStringLandmark($letter, '')
            ) {
                $oldPos = $i;
                $i = $this->skipString($letter, $i + 1, $instruction, $startsTemplate);
                $properInstr .= $instruction->iSubStr($oldPos, $i - 1);
                if (\is_null($instruction->getLetter($i))) {
                    break;
                }
                $letter = $instruction->getLetter($i);
            }

            if ($letter === "\n") {
                $properInstr .= ' ';
                continue;
            }

            $nextLetter = $instruction->getLetter($i + 1) ?? '';

            if (
                Validate::isWhitespace($letter) && Validate::isSpecial($nextLetter)
                || Validate::isWhitespace($letter) && Validate::isWhitespace($nextLetter)
            ) {
                continue;
            }

            //  Fix for all special symbol prefixed with ';'
            $blackListedSpecial = [
                "}" => true,
                "]" => true,
                ")" => true,
                "!" => true,
                ' ' => true,
            ];
            if (
                $letter == ';'
                && isset($blackListedSpecial[$nextLetter])
            ) {
                continue;
            }
            $properInstr .= $letter;
        }
        return trim($properInstr);
    }

    protected function canBeAliased(string $name, BlockAbstract $block): bool
    {
        $reserved = [];
        if ($block instanceof Block\ClassMethodBlock) {
            $reserved['constructor'] = false;
        }

        return $reserved[$name] ?? true;
    }

    protected function createSubBlocksWithContent(string $content, $special = false): array
    {
        $caret = $this->getCaret();
        self::$content->addContent($content);
        $blocks = $this->createSubBlocks(0, $special);
        self::$content->removeContent();
        $this->setCaret($caret);
        return $blocks;
    }

    protected function getNextLetter(int $start, Content $content): array
    {
        for ($i=$start; $i < $content->getLength(); $i++) {
            $letter = $content->getLetter($i);
            if (!Validate::isWhitespace($letter)) {
                return [$letter, $i];
            }
        }

        return ['', $i - 1];
    }

    protected function getPreviousLetter(int $start, Content $content): array
    {
        for ($i=$start; $i >= 0; $i--) {
            $letter = $content->getLetter($i);
            if (!Validate::isWhitespace($letter)) {
                return [$letter, $i];
            }
        }

        return ['', 0];
    }

    protected function getPreviousWord(int $start, Content $content): array
    {
        $letterFound = false;
        $whitespaceFound = false;
        $word = '';
        for ($i=$start; $i >= 0; $i--) {
            $letter = $content->getLetter($i);
            if (Validate::isWhitespace($letter)) {
                if (!$whitespaceFound) {
                    $whitespaceFound = true;
                } elseif ($letterFound) {
                    return [Str::rev($word), $i];
                }
                continue;
            }

            if ($letterFound) {
                $word .= $letter;
            }

            if ($whitespaceFound && !$letterFound) {
                $word .= $letter;
                $letterFound = true;
                continue;
            }
        }

        return [Str::rev($word), 0];
    }

    protected function getNextWord(int $start, Content $content): array
    {
        $letterFound = false;
        $whitespaceFound = false;
        $word = '';
        for ($i=$start; $i < $content->getLength(); $i++) {
            $letter = $content->getLetter($i);
            if (Validate::isWhitespace($letter)) {
                if (!$whitespaceFound) {
                    $whitespaceFound = true;
                } elseif ($letterFound) {
                    return [$word, $i - 1];
                }
                continue;
            }

            if ($letterFound) {
                $word .= $letter;
            }

            if ($whitespaceFound && !$letterFound) {
                $word .= $letter;
                $letterFound = true;
                continue;
            }
        }

        return [$word, 0];
    }

    protected function isNextSiblingContected(): bool
    {

        $nextSibling = $this->getClosestNextChild();
        if (!$nextSibling) {
            return false;
        }
        return $nextSibling instanceof Block\CallerBlock
            || ($nextSibling instanceof Block\ChainLinkBlock && $nextSibling->getSubtype() !== Block\ChainLinkBlock::FIRST)
            || $nextSibling instanceof Block\BracketChainLinkBlock
            || $nextSibling instanceof Block\DoubleEqualBlock
            || $nextSibling instanceof Block\TripleEqualBlock
            || $nextSibling instanceof Block\OperatorBlock
            || $nextSibling instanceof Block\SymbolBlock
        ;
    }

    protected function getClosestNextChild(): bool | BlockInterface
    {
        $parent = $this->getParent();
        try {
            $childIndex = $this->getChildIndex();
        } catch (\Error $e) {
            // If we didn't set childIndex it means it still in progress of objectifing
            return false;
        }

        if (is_null($parent)) {
            return false;
        }
        if ((
            $parent::class === Block\ChainLinkBlock::class
            && (
                $parent->getSubtype() == Block\ChainLinkBlock::END_METHOD
                || $parent->getSubtype() == Block\ChainLinkBlock::END_VARIABLE
            )
        ) || (
            $parent::class === Block\BracketChainLinkBlock::class
            && (
                $parent->getSubtype() == Block\BracketChainLinkBlock::METHOD
                || $parent->getSubtype() == Block\BracketChainLinkBlock::VARIABLE
            )
        )) {
            $child = $parent->getMethodValues();
        } else {
            $placementMethod = $this->getPlacement();
            $child = $parent->$placementMethod()[$childIndex] ?? throw new Exception('Child not found', 404);
        }
        if ($child instanceof Block\ScriptBlock) {
            return false;
        }

        $nextSibling = $parent->getBlocks()[$childIndex + 1] ?? false;
        if (!$nextSibling) {
            return $parent->getClosestNextChild();
        }
        return $nextSibling;
    }

    protected function isAnyOpenBracket(string $letter): bool
    {
        $brackets = [
            '{' => true,
            '[' => true,
            '(' => true,
        ];
        return $brackets[$letter] ?? false;
    }

    protected function isAnyCloseBracket(string $letter): bool
    {
        $brackets = [
            '}' => true,
            ']' => true,
            ')' => true,
        ];
        return $brackets[$letter] ?? false;
    }

    protected function skipIfNeccessary(Content $content, string $letter, int $i): array
    {
        $oldI = $i;
        if (
            ($startsTemplate = Validate::isTemplateLiteralLandmark($letter, ''))
            || Validate::isStringLandmark($letter, '')
        ) {
            $oldPos = $i;
            $i = $this->skipString($letter, $i + 1, $content, $startsTemplate);
            if (\is_null($content->getLetter($i))) {
                return [$content->getLetter($i - 1), $i - 1];
            }
            $letter = $content->getLetter($i);
        }

        if ($this->isAnyOpenBracket($letter)) {
            $this->bracketsCount++;
            $i++;
        } elseif ($this->isAnyCloseBracket($letter) && $this->bracketsCount > 0) {
            $this->bracketsCount--;
            $i++;
        } elseif ($this->bracketsCount > 0) {
            $i++;
        }

        if (\is_null($content->getLetter($i))) {
            return [$content->getLetter($i - 1), $i - 1];
        }

        $newLetter = $content->getLetter($i);
        if ($oldI != $i) {
            return $this->skipIfNeccessary($content, $newLetter, $i);
        }

        return [$newLetter, $i];
    }

    protected function removeComments(Content $content): Content
    {
        $purgedContentArray = [];
        $commentInProggress = false;
        $commentType = '';
        $lastStart = 0;
        for ($i=0; $i < $content->getLength(); $i++) {
            $currentLetter   = $content->getLetter($i);
            $nextLetter      = $content->getLetter($i + 1);
            $possibleComment = $currentLetter . $nextLetter;

            if (
                $commentInProggress
                && (
                    $commentType === 'multi' && $possibleComment === "*/"
                    || $commentType === 'single' && $currentLetter === "\n"
                )
            ) {
                if ($commentType === 'multi') {
                    $lastStart = $i + 2;
                } else {
                    $lastStart = $i;
                }
                $commentInProggress = false;
                continue;
            }

            if (
                !$commentInProggress
                && (
                    $possibleComment === "/*"
                    || $possibleComment === "//"
                )
            ) {
                if ($possibleComment === "/*") {
                    $commentType = 'multi';
                } else {
                    $commentType = 'single';
                }
                $commentInProggress = true;
                $purgedContentArray = array_merge($purgedContentArray, $content->iCutToArray($lastStart, $i - 2));
                // skip two
                $i++;
                continue;
            }
        }
        $purgedContentArray = array_merge($purgedContentArray, $content->iCutToArray($lastStart, $i - 2));

        return (new Content(''))->addArrayContent($purgedContentArray, true);
    }

    public function getScript(): Block\ScriptBlock
    {
        if ($this instanceof Block\ScriptBlock) {
            return $this;
        }

        $parent = $this->getParent();

        if (!($parent instanceof BlockInterface)) {
            throw new Exception('Script block not found', 404);
        }

        return $parent->getScript();
    }

    public function addBlock(BlockInterface $block): self
    {
        $this->blocks[] = $block;
        return $this;
    }

    public static function createBlock(
        string $className,
        string $content,
        int $start = 0,
        string $subtype = '',
        ?BlockInterface $parent = null
    ): BlockInterface {
        $class = self::blockExistsOrThrow($className);
        if (isset(self::$content)) {
            self::$content->addContent('');
            $newBlock = new $class($start, $subtype, $parent);
            self::$content->removeContent();
        } else {
            self::$content = new Content('');
            $newBlock = new $class($start, $subtype, $parent);
            unset(self::$content);
        }
        return $newBlock;
    }

    public static function blockExistsOrThrow(string $className): string
    {
        $prefix = 'Tetraquark\Block\\';
        $class  = $prefix . $className;

        if (!\class_exists($class)) {
            throw new Exception("Passed class doesn't exist: " . htmlspecialchars($className), 404);
        }
        return $class;
    }
}
