<?php declare(strict_types=1);

namespace Tetraquark\Abstract;

use \Tetraquark\Trait\{BlockGetSetTrait, BlockMapsTrait, BlockAliasMapTrait};
use \Tetraquark\Abstract\{
    CommentBlockAbstract as CommentBlock,
    ConditionBlockAbstract as ConditionBlock,
    MethodBlockAbstract as MethodBlock,
    VariableBlockAbstract as VariableBlock
};
use \Tetraquark\{Exception as Exception, Block as Block, Log as Log, Validate as Validate};

abstract class BlockAbstract
{
    // I've seperated related functionality to Traits to make this file more managable
    use BlockGetSetTrait;   // Holds all get and set functions
    use BlockMapsTrait;     // Has $blocksMap, $classBlocksMap, $objectBlocksMap, $callerBlocksMap and $arrayBlocksMap variables
    use BlockAliasMapTrait; // Contains our alias creation map
    static protected string $content;
    static protected array  $mappedAliases = [];
    protected int    $caret = 0;
    protected bool   $endFunction = false;

    /** @var string $instruction Actual block representation in code */
    protected string $instruction;

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
        protected int $start = 0,
        string $subtype = '',
        protected array $data  = []
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
            $mappedWord = substr($mappedWord, 0, -1);
            $possibleUndefined = substr($possibleUndefined, 0, -1);
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
                $startBlock = $lastBlock->getInstructionStart() + \mb_strlen($lastBlock->getInstruction());
                for ($i=$startBlock; $i < \mb_strlen(self::$content); $i++) {
                    $letter = self::$content[$i];
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
                    )
                )
            ) {
                $block = new $class($start, Block\ChainLinkBlock::FIRST);

                $possibleUndefined = \mb_substr($possibleUndefined, 0, -(\mb_strlen($block->getInstruction()) + 1));
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
        for ($i=$start; $i < strlen(self::$content); $i++) {
            $letter = self::$content[$i];

            if (
                $skipString &&
                (
                    ($startsTemplate = Validate::isTemplateLiteralLandmark($letter, ''))
                    || Validate::isStringLandmark($letter, '')
                )
            ) {
                $i = $this->skipString($i + 1, self::$content, $startsTemplate);
                $letter = self::$content[$i];
            }

            if ($endChars[$letter] ?? false) {
                $properEnd = $i + 1;
                $this->setCaret($properEnd);
                break;
            }
        }

        if (is_null($properEnd)) {
            throw new Exception('Proper End not found', 404);
        }

        $properStart = $start - strlen($name);
        $instruction = substr(self::$content, $properStart, $properEnd - $properStart);
        $this->setInstructionStart($properStart)
            ->setInstruction($instruction);
    }

    protected function findInstructionStart(int $end, ?array $endChars = null): void
    {
        if (\is_null($endChars)) {
            $endChars = $this->endChars;
        }

        $properStart = null;
        for ($i=$end - 1; $i >= 0; $i--) {
            $letter = self::$content[$i];
            if (
                ($startsTemplate = Validate::isTemplateLiteralLandmark($letter, ''))
                || Validate::isStringLandmark($letter, '')
            ) {
                $i = $this->skipString($i - 1, self::$content, $startsTemplate, true);
                $letter = self::$content[$i];
            }

            if ($endChars[$letter] ?? false) {
                $properStart = $i + 1;
                break;
            }
        }

        if (is_null($properStart)) {
            $properStart = 0;
        }

        $instruction = trim(substr(self::$content, $properStart, $end - $properStart));
        $this->setInstructionStart($properStart)
            ->setInstruction($instruction);
    }

    protected function constructBlock(string $mappedWord, string $className, int &$i, string &$possibleUndefined, array &$blocks): ?BlockAbstract
    {
        $block = $this->blockFactory($mappedWord, $className, $i, $possibleUndefined, $blocks);
        $i = $block->getCaret();
        return $block;
    }

    protected function createSubBlocks(?int $start = null): array
    {
        if (is_null($start)) {
            $start = $this->getCaret();
        }

        $map = null;
        $mappedWord = '';
        $possibleUndefined = '';
        $undefinedEnds = ["\n" => true, ";" => true];
        $blocks = [];
        for ($i=$start; $i < \mb_strlen(self::$content); $i++) {
            $letter = self::$content[$i];
            if (
                ($startsTemplate = Validate::isTemplateLiteralLandmark($letter, ''))
                || Validate::isStringLandmark($letter, '')
            ) {
                $oldPos = $i;
                $i = $this->skipString($i + 1, self::$content, $startsTemplate);

                if (Validate::isValidUndefined($possibleUndefined)) {
                    $blocks[] = new Block\UndefinedBlock($oldPos - \mb_strlen($possibleUndefined), $possibleUndefined);
                }
                if (!$this instanceof Block\ObjectBlock) {
                    $blocks[] = new Block\StringBlock($oldPos, \mb_substr(self::$content, $oldPos, $i - $oldPos));
                }

                if (!isset(self::$content[$i])) {
                    $possibleUndefined = '';
                    break;
                }

                $letter = self::$content[$i];
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
                $possibleUndefined = substr($possibleUndefined, 0, -1);
                if (Validate::isValidUndefined($possibleUndefined)) {
                    $blocks[] = new Block\UndefinedBlock($i - \mb_strlen($possibleUndefined), $possibleUndefined);
                    $possibleUndefined = '';
                }
                break;
            }
        }

        if ($i == \mb_strlen(self::$content)) {
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
        $start = \strlen($prefix) - 1;
        if ($start < 0) {
            $start = 0;
        }

        for ($i=$start; $i < strlen($instr); $i++) {
            $letter = $instr[$i];
            if ($ends[$letter] ?? false) {
                $this->setName(substr($instr, $start, $i - $start));
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
        for ($i=0; $i < \mb_strlen($value); $i++) {
            $letter = $value[$i];
            $isLiteralLandmark = Validate::isTemplateLiteralLandmark($letter, $value[$i - 1] ?? null, $templateLiteralInProgress);
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
                continue;
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

    protected function startsTemplateLiteralVariable(string $letter, string $value, int $i): bool
    {
        return ($value[$i - 1] ?? '') . $letter == '${'
            && ($value[$i - 2] ?? '') . ($value[$i - 1] ?? '') . $letter != '\${';
    }

    public function skipString(int $start, string $value, bool $isTemplate = false, bool $reverse = false): int
    {
        $modifier = (((int)!$reverse) * 2) - 1;
        for ($i=$start; (!$reverse && $i < \mb_strlen($value)) || ($reverse && $i >= 0); $i += $modifier) {
            $letter = $value[$i];
            if ($isTemplate && Validate::isTemplateLiteralLandmark($letter, $value[$i - 1] ?? '', true)) {
                return $i + $modifier;
            } elseif (!$isTemplate && Validate::isStringLandmark($letter, $value[$i - 1] ?? '', true)) {
                return $i + $modifier;
            }
        }
        return $i;
    }

    protected function removeAdditionalSpaces(string $instruction): string
    {
        $properInstr = '';
        for ($i=0; $i < \mb_strlen($instruction); $i++) {
            $letter = $instruction[$i];
            if (
                ($startsTemplate = Validate::isTemplateLiteralLandmark($letter, ''))
                || Validate::isStringLandmark($letter, '')
            ) {
                $oldPos = $i;
                $i = $this->skipString($i + 1, $instruction, $startsTemplate);
                $properInstr .= \mb_substr($instruction, $oldPos, $i - $oldPos);
                if (!isset($instruction[$i])) {
                    break;
                }
                $letter = $instruction[$i];
            }

            if (
                Validate::isWhitespace($letter) && Validate::isSpecial($instruction[$i + 1] ?? '')
                || Validate::isWhitespace($letter) && Validate::isWhitespace($instruction[$i + 1] ?? '')
            ) {
                continue;
            }
            $properInstr .= $letter;
        }
        return trim($properInstr);
    }

    protected function decideArrayBlockType(int $start) {
        for ($i=$start - 1; $i >= 0; $i--) {
            $letter = self::$content[$i];
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
        $codeSave = self::$content;
        self::$content = $content;
        $blocks = $this->createSubBlocks(0);
        self::$content = $codeSave;
        $this->setCaret($caret);
        return $blocks;
    }

    protected function getNextLetter(int $start, string $content): array
    {
        for ($i=$start; $i < \mb_strlen($content); $i++) {
            $letter = $content[$i];
            if (!Validate::isWhitespace($letter)) {
                return [$letter, $i];
            }
        }

        return ['', $i - 1];
    }

    protected function getPreviousLetter(int $start, string $content): array
    {
        for ($i=$start; $i >= 0; $i--) {
            $letter = $content[$i];
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
        $instruction = $nextSibling->getInstruction();
        $letter = trim($instruction)[0] ?? '';
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

    protected function skipIfNeccessary(string $content, string $letter, int $i): array
    {
        if (
            ($startsTemplate = Validate::isTemplateLiteralLandmark($letter, ''))
            || Validate::isStringLandmark($letter, '')
        ) {
            $oldPos = $i;
            $i = $this->skipString($i + 1, $content, $startsTemplate);
            $letter = $content[$i];
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
        $newLetter = $content[$i];
        if ($newLetter != $letter) {
            return $this->skipIfNeccessary($content, $newLetter, $i);
        }

        return [$newLetter, $i];
    }
}
