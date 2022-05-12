<?php declare(strict_types=1);

namespace Tetraquark\Foundation;

use \Tetraquark\Trait\{BlockGetSetTrait, BlockMapsTrait, BlockAliasMapTrait};
use \Tetraquark\Foundation\{
    CommentBlockAbstract as CommentBlock,
    ConditionBlockAbstract as ConditionBlock,
    MethodBlockAbstract as MethodBlock,
    VariableBlockAbstract as VariableBlock
};
use \Tetraquark\{Exception, Block, Log, Validate, Str, Content};

abstract class BlockAbstract
{
    // I've seperated related functionality to Traits to make this file more managable
    use BlockGetSetTrait;   // Holds all get and set functions
    use BlockMapsTrait;     // Has $blocksMap, $classBlocksMap, $objectBlocksMap, $callerBlocksMap and $arrayBlocksMap variables
    use BlockAliasMapTrait; // Contains our alias creation map
    static protected Content $content;
    static protected array   $mappedAliases = [];
    protected int    $caret = 0;
    protected bool   $endFunction = false;

    /** @var Content $instruction Actual block representation in code */
    protected Content $instruction;

    protected int    $instructionStart;
    protected string $name;

    /** @var int Queue indicator */
    protected int    $childIndex;

    /** @var BlockAbstract Parent of this block */
    protected BlockAbstract $parent;

    protected array  $aliasesMap = [];

    /** @var BlockAbstract[] $blocks Array of Blocks */
    protected array  $blocks = [];

    protected array  $endChars = [
        "\n" => true,
        ";" => true,
    ];

    /** @var int Amount of opened brackets */
    protected int $bracketsCount = 0;

    public function __construct(
        int $start = 0,
        string $subtype = '',
    ) {
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

    protected function blockFactory(string $hint, string $className, int $start, string &$possibleUndefined, array &$blocks): BlockAbstract
    {
        $prefix = 'Tetraquark\Block\\';
        $class  = $prefix . $className;

        if (!\class_exists($class)) {
            throw new Exception("Passed class doesn't exist: " . htmlspecialchars($className), 404);
        }

        // If this is variable creatiion without any type declaration then its attribute assignment and we shouldn't add anything before it
        if ($hint == '=') {
            $hint = '';
        }

        if ($class == Block\ChainLinkBlock::class) {
            $lastBlock = $blocks[\sizeof($blocks) - 1] ?? null;

            $first = false;
            // Check if we are not between some equasion with at least two ChainBlocks
            if ($lastBlock) {
                $startBlock = $lastBlock->getInstructionStart() + $lastBlock->getInstruction()->getLength();
                for ($i=$startBlock; $i < self::$content->getLength(); $i++) {
                    $letter = self::$content->getLetter($i);
                    if ($letter == ' ') {
                        continue;
                    }
                    if (Validate::isSpecial($letter) || $letter == "\n") {
                        $first = true;
                    }
                    break;
                }
            }

            if (
                $first
                || !($lastBlock instanceof Block\ChainLinkBlock)
                || (
                    $lastBlock instanceof Block\ChainLinkBlock
                    && (
                        $lastBlock->getSubtype() == Block\ChainLinkBlock::END_METHOD
                        || $lastBlock->getSubtype() == Block\ChainLinkBlock::END_VARIABLE
                        || $lastBlock->getSubtype() == '.'
                    )
                )
            ) {
                $block = new $class($start, Block\ChainLinkBlock::FIRST);

                $possibleUndefined = \mb_substr($possibleUndefined, 0, -($block->getInstruction()->getLength() + 1));
                if (Validate::isValidUndefined($possibleUndefined)) {
                    $blocks[] = new Block\UndefinedBlock($start - \mb_strlen($possibleUndefined), $possibleUndefined);
                }

                $blocks[] = $block;
                $possibleUndefined = '';
            }
            return new $class($start + 1, $hint);
        }
        return new $class($start, $hint);
    }

    protected function findInstructionEnd(int $start, string $name, ?array $endChars = null, bool $skipString = true): void
    {
        if (\is_null($endChars)) {
            $endChars = $this->endChars;
        }

        $properEnd = null;
        for ($i=$start; $i < self::$content->getLength(); $i++) {
            $letter = self::$content->getLetter($i);

            if (
                $skipString &&
                (
                    ($startsTemplate = Validate::isTemplateLiteralLandmark($letter, ''))
                    || Validate::isStringLandmark($letter, '')
                )
            ) {
                $i = $this->skipString($i + 1, self::$content, $startsTemplate);
                $letter = self::$content->getLetter($i);
            }

            if ($endChars[$letter] ?? false) {
                $properEnd = $i + 1;
                $this->setCaret($properEnd);
                break;
            }
        }

        if (is_null($properEnd)) {
            $properEnd = self::$content->getLength() - 1;
            $this->setCaret($properEnd);
        }

        $properStart = $start - \mb_strlen($name);
        $this->setInstructionStart($properStart)
            ->setInstruction(self::$content->iCutToContent($properStart, $properEnd));
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
                // @TODO
                $i = $this->skipString($i - 1, self::$content, $startsTemplate, true);
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
            ->setInstruction(self::$content->iCutToContent($properStart, $end - $properStart));
    }

    protected function constructBlock(string $mappedWord, string $className, int &$i, string &$possibleUndefined, array &$blocks): ?BlockAbstract
    {
        $block = $this->blockFactory($mappedWord, $className, $i, $possibleUndefined, $blocks);
        $i = $block->getCaret();
        return $block;
    }

    protected function createSubBlocks(?int $start = null, $special = false): array
    {
        if (is_null($start)) {
            $start = $this->getCaret();
        }

        $map = null;
        $mappedWord = '';
        $possibleUndefined = '';
        $undefinedEnds = ["\n" => true, ";" => true];
        $blocks = [];
        for ($i=$start; $i < self::$content->getLength(); $i++) {
            $letter = self::$content->getLetter($i);
            if (
                ($startsTemplate = Validate::isTemplateLiteralLandmark($letter, ''))
                || Validate::isStringLandmark($letter, '')
            ) {
                $oldPos = $i;
                // @TODO
                $i = $this->skipString($i + 1, self::$content, $startsTemplate);

                if (Validate::isValidUndefined($possibleUndefined)) {
                    $blocks[] = new Block\UndefinedBlock($oldPos - \mb_strlen($possibleUndefined), $possibleUndefined);
                }
                if (!$this instanceof Block\ObjectBlock) {
                    $blocks[] = new Block\StringBlock($oldPos, self::$content->iSubStr($oldPos, $i));
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
                $oldPos = $i - \mb_strlen($possibleUndefined);
                $block = $this->constructBlock($mappedWord, $map, $i, $possibleUndefined, $blocks);
                $block->setChildIndex(\sizeof($blocks));
                $block->setParent($this);
                $mappedWordLen = \mb_strlen($mappedWord);
                $instStart = $block->getInstructionStart();
                // Check if instruction did include semicolon
                // @TODO
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
                    $blocks[] = new Block\UndefinedBlock($oldPos - \mb_strlen($possibleUndefined), $possibleUndefined);
                }

                $possibleUndefined = '';
                $blocks[] = $block;
                $mappedWord = '';
                $map = null;
                continue;
            } elseif (\is_null($map)) {
                $mappedWord = '';
            }

            if ($this->endChars[$letter] ?? false) {
                $possibleUndefined = \mb_substr($possibleUndefined, 0, -1);
                if (Validate::isValidUndefined($possibleUndefined)) {
                    $blocks[] = new Block\UndefinedBlock($i - \mb_strlen($possibleUndefined), $possibleUndefined);
                    $possibleUndefined = '';
                }
                break;
            }
        }

        if ($i == self::$content->getLength()) {
            $i--;
        }

        if (Validate::isValidUndefined($possibleUndefined)) {
            $blocks[] = new Block\UndefinedBlock($i - \mb_strlen($possibleUndefined), $possibleUndefined);
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
                $this->setName($instr->iSubStr($start, $i));
                return;
            }
        }
        if ($this instanceof Block\FunctionBlock) {
            $this->setName('');
            return;
        }
        throw new Exception('Blocks name not found', 404);
    }

    protected function generateAliases(string $lastAlias = ''): string
    {
        // Firstly set aliases to all blocks on this level
        foreach ($this->blocks as $block) {
            if ($this->aliasExists($block->getName()) || !$this->canBeAliased($block->getName(), $block)) {
                continue;
            }

            $alias = $this->generateAlias($block->getName(), $lastAlias);

            if (\mb_strlen($alias) == 0) {
                continue;
            }

            $this->setAlias($block->getName(), $alias);
            $lastAlias = $alias;
        }

        if ($this instanceof MethodBlock && !($this instanceof Block\NewClassBlock)) {
            foreach ($this->getArguments() as $argument) {
                foreach ($argument as $block) {
                    if ($this->aliasExists($block->getName()) || !$this->canBeAliased($block->getName(), $block)) {
                        continue;
                    }

                    $alias = $this->generateAlias($block->getName(), $lastAlias);

                    if (\mb_strlen($alias) == 0) {
                        continue;
                    }

                    $this->setAlias($block->getName(), $alias);
                    $lastAlias = $alias;

                    foreach ($block->getBlocks() as $subBlock) {
                        $lastAlias = $subBlock->generateAliases($lastAlias);
                    }
                }
            }

        } elseif ($this instanceof ConditionBlock) {
            foreach ($this->getCondBlocks() as $block) {
                if ($this->aliasExists($block->getName()) || !$this->canBeAliased($block->getName(), $block)) {
                    continue;
                }

                $alias = $this->generateAlias($block->getName(), $lastAlias);

                if (\mb_strlen($alias) == 0) {
                    continue;
                }

                $this->setAlias($block->getName(), $alias);
                $lastAlias = $alias;

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

    protected function generateAlias(string $name, string $lastAlias): string
    {
        if (\mb_strlen($name) == 0) {
            return '';
        }
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

    protected function replaceVariablesWithAliases(string $value): string
    {
        $word = '';
        $minifiedValue = '';
        $stringInProgress = false;
        $templateVarInProgress = false;
        $templateLiteralInProgress = false;
        Str::iterate(
            $value,
            0,
            ['', '', false, false, false],
            function(
                string $letter, int $i, string &$word,
                string &$minifiedValue, bool &$stringInProgress,
                bool &$templateVarInProgress, bool &$templateLiteralInProgress
            ) use ($value): void {
                Log::log('Letter: ' . ($letter ?? 'NULL') . ', ' . $word);
                $isLiteralLandmark = Validate::isTemplateLiteralLandmark($letter, $value[$i - 1] ?? null, $templateLiteralInProgress);
                if ($templateVarInProgress && !$isLiteralLandmark) {
                    if ($letter == '}') {
                        $templateVarInProgress = false;
                        $alias = $this->getAlias($word);
                        $minifiedValue .= $alias . $letter;
                        $word = '';
                        return;
                    } else {
                        $word .= $letter;
                    }
                    return;
                }

                if ($isLiteralLandmark) {
                    $templateLiteralInProgress = !$templateLiteralInProgress;
                } elseif (Validate::isStringLandmark($letter, $value[$i - 1] ?? null, $stringInProgress)) {
                    $stringInProgress = !$stringInProgress;
                }

                if (
                    $templateLiteralInProgress
                    && $this->startsTemplateLiteralVariable($letter, $value, $i)
                ) {
                    $templateVarInProgress = true;
                }

                if ($stringInProgress || $templateLiteralInProgress) {
                    $minifiedValue .= $letter;
                    $word = '';
                    return;
                }

                if (Validate::isSpecial($letter)) {
                    $alias = $this->getAlias($word);
                    $minifiedValue .= $alias . $letter;
                    $word = '';
                    return;
                }

                $word .= $letter;
            }
        );

        $alias = $this->getAlias($word);
        return $minifiedValue . $alias;
    }

    protected function startsTemplateLiteralVariable(string $letter, Content $value, int $i): bool
    {
        return ($value->getLetter($i - 1) ?? '') . $letter == '${'
            && ($value->getLetter($i - 2) ?? '') . ($value->getLetter($i - 1) ?? '') . $letter != '\${';
    }

    public function skipString(int $start, Content $content, bool $isTemplate = false, bool $reverse = false): int
    {
        $modifier = (((int)!$reverse) * 2) - 1;
        for ($i=$start; (!$reverse && $i < $content->getLength()) || ($reverse && $i >= 0); $i += $modifier) {
            $letter = $value->getLetter($i);
            if ($isTemplate && Validate::isTemplateLiteralLandmark($letter, $value->getLetter($i - 1) ?? '', true)) {
                return $i + $modifier;
            } elseif (!$isTemplate && Validate::isStringLandmark($letter, $value->getLetter($i - 1) ?? '', true)) {
                return $i + $modifier;
            }
        }
        return $i;
    }

    protected function removeAdditionalSpaces(Content $instruction): string
    {
        $properInstr = '';
        for ($i=0; $i < $instruction->getLength(); $i++) {
            $letter = $instruction->getLetter($i);
            if (
                ($startsTemplate = Validate::isTemplateLiteralLandmark($letter, ''))
                || Validate::isStringLandmark($letter, '')
            ) {
                $oldPos = $i;
                $i = $this->skipString($i + 1, $instruction, $startsTemplate);
                $properInstr .= \mb_substr($instruction, $oldPos, $i - $oldPos);
                if (\is_null($instruction->getLetter($i))) {
                    break;
                }
                $letter = $instruction->getLetter($i);
            }

            if (
                Validate::isWhitespace($letter) && Validate::isSpecial($instruction->getLetter($i + 1) ?? '')
                || Validate::isWhitespace($letter) && Validate::isWhitespace($instruction->getLetter($i + 1) ?? '')
            ) {
                continue;
            }
            $properInstr .= $letter;
        }
        return trim($properInstr);
    }

    protected function decideArrayBlockType(int $start) {
        for ($i=$start - 1; $i >= 0; $i--) {
            $letter = self::$content->getLetter($i);
            if (Validate::isWhitespace($letter)) {
                continue;
            }
            if (!Validate::isSpecial($letter)) {
                return 'BracketChainLinkBlock';
            }
            return "ArrayBlock";
        }
        return "ArrayBlock";
    }

    protected function canBeAliased(string $name, BlockAbstract $block): bool
    {
        $reserved = [];
        if ($block instanceof Block\ClassMethodBlock) {
            $reserved['constructor'] = false;
        }

        return $reserved[$name] ?? true;
    }

    protected function createSubBlocksWithContent(string $content): array
    {
        $caret = $this->getCaret();
        self::$content->setContent($content);
        $blocks = $this->createSubBlocks(0);
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

        return ['', $i - 1];
    }

    protected function checkIfFirstLetterInNextSiblingIsADot(): bool
    {
        $parent = $this->getParent();
        $childIndex = $this->getChildIndex();
        $nextSibling = $parent->getBlocks()[$childIndex + 1] ?? false;
        if (!$nextSibling instanceof BlockAbstract || $nextSibling instanceof CommentBlock) {
            return false;
        }
        $letter = $nextSibling->getInstruction()->trim()->getLetter(0) ?? '';
        return $letter === '.';
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
        if (
            ($startsTemplate = Validate::isTemplateLiteralLandmark($letter, ''))
            || Validate::isStringLandmark($letter, '')
        ) {
            $oldPos = $i;
            $i = $this->skipString($i + 1, $content, $startsTemplate);
            if (\is_null($content->getLetter($i))) {
                return [$content->getLetter($i - 1), $i - 1];
            }
            $letter = $content->getLetter($i);
        }

        if ($this->isAnyOpenBracket($letter)) {
            $this->bracketsCount++;
            $i++;
        } elseif ($this->isAnyCloseBracket($letter)) {
            $this->bracketsCount--;
            $i++;
        } elseif ($this->bracketsCount > 0) {
            $i++;
        }

        if (\is_null($content->getLetter($i))) {
            return [$content->getLetter($i - 1), $i - 1];
        }

        $newLetter = $content->getLetter($i);
        if ($newLetter != $letter) {
            return $this->skipIfNeccessary($content, $newLetter, $i);
        }

        return [$newLetter, $i];
    }
}
