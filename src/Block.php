<?php declare(strict_types=1);

namespace Tetraquark;
use \Xeno\X as Xeno;

abstract class Block
{
    static protected string $content;
    static protected array  $mappedAliases = [];
    protected int    $caret = 0;
    protected bool   $endFunction = false;
    /** @var string $instruction Actual block representation in code */
    protected string $instruction;
    protected int    $instructionStart;
    protected string $name;
    protected array  $aliasesMap = [];
    /** @var Block[] $blocks Array of Blocks */
    protected array  $blocks = [];
    protected array  $endChars = [
        "\n" => true,
        "\r" => true,
        ";" => true,
    ];
    /** @var array Map of possible aliases (df is to get default - the start of map), the last alias direction returns false */
    protected array $aliasMap = [
        'df' => 'a', 'a' => 'b', 'b' => 'c', 'c' => 'd', 'd' => 'e', 'e' => 'f', 'f' => 'g', 'g' => 'h', 'h' => 'i', 'i' => 'j', 'j' => 'k', 'k' => 'l', 'l' => 'm', 'm' => 'n', 'n' => 'o',
        'o' => 'p', 'p' => 'r', 'r' => 's', 's' => 't', 't' => 'u', 'u' => 'w', 'w' => 'z', 'z' => 'y', 'y' => 'x', 'x' => 'q', 'q' => 'v', 'v' => 'µ', 'µ' => 'ß', 'ß' => 'à', 'à' => 'á', 'á' => 'â',
        'â' => 'ã', 'ã' => 'ä', 'ä' => 'å', 'å' => 'æ', 'æ' => 'ç', 'ç' => 'è', 'è' => 'é', 'é' => 'ê', 'ê' => 'ë', 'ë' => 'ì', 'ì' => 'í', 'í' => 'î', 'î' => 'ï', 'ï' => 'ð', 'ð' => 'ñ', 'ñ' => 'ò',
        'ò' => 'ó', 'ó' => 'ô', 'ô' => 'õ', 'õ' => 'ö', 'ö' => 'ø', 'ø' => 'ù', 'ù' => 'ú', 'ú' => 'û', 'û' => 'ü', 'ü' => 'ý', 'ý' => 'þ', 'þ' => 'ÿ', 'ÿ' => 'ā', 'ā' => 'ă', 'ă' => 'ą', 'ą' => 'ć',
        'ć' => 'ĉ', 'ĉ' => 'ċ', 'ċ' => 'č', 'č' => 'ď', 'ď' => 'đ', 'đ' => 'ē', 'ē' => 'ĕ', 'ĕ' => 'ė', 'ė' => 'ę', 'ę' => 'ě', 'ě' => 'ĝ', 'ĝ' => 'ğ', 'ğ' => 'ġ', 'ġ' => 'ģ', 'ģ' => 'ĥ', 'ĥ' => 'A',
        'A' => 'B', 'B' => 'C', 'C' => 'D', 'D' => 'E', 'E' => 'F', 'F' => 'G', 'G' => 'H', 'H' => 'I', 'I' => 'J', 'J' => 'K', 'K' => 'L', 'L' => 'M', 'M' => 'N', 'N' => 'O',
        'O' => 'P', 'P' => 'Q', 'Q' => 'R', 'R' => 'S', 'S' => 'T', 'T' => 'U', 'U' => 'V', 'V' => 'W', 'W' => 'X', 'X' => 'Y', 'Y' => 'Z', 'Z' => 'À', 'À' => 'Á', 'Á' => 'Â', 'Â' => 'Ã', 'Ã' => 'Ä',
        'Ä' => 'Å', 'Å' => 'Æ', 'Æ' => 'Ç', 'Ç' => 'È', 'È' => 'É', 'É' => 'Ê', 'Ê' => 'Ë', 'Ë' => 'Ì', 'Ì' => 'Í', 'Í' => 'Î', 'Î' => 'Ï', 'Ï' => 'Ð', 'Ð' => 'Ñ', 'Ñ' => 'Ò', 'Ò' => 'Ó', 'Ó' => 'Ô',
        'Ô' => 'Õ', 'Õ' => 'Ö', 'Ö' => 'Ø', 'Ø' => 'Ù', 'Ù' => 'Ú', 'Ú' => 'Û', 'Û' => 'Ü', 'Ü' => 'Ý', 'Ý' => 'Þ', 'Þ' => 'Ā', 'Ā' => 'Ă', 'Ă' => 'Ą', 'Ą' => 'Ć', 'Ć' => 'Ĉ', 'Ĉ' => 'Ċ', 'Ċ' => 'Č',
        'Č' => 'Ď', 'Ď' => 'Đ', 'Đ' => 'Ē', 'Ē' => 'Ĕ', 'Ĕ' => 'Ė', 'Ė' => 'Ę', 'Ę' => 'Ě', 'Ě' => 'Ĝ', 'Ĝ' => 'Ğ', 'Ğ' => 'Ġ', 'Ġ' => 'Ģ', 'Ģ' => 'Ĥ', 'Ĥ' => 'Ħ', 'Ħ' => 'Ĩ', 'Ĩ' => 'Ī', 'Ī' => '$',
        '$' => '_', '_' => false
    ];


    /**
     * Map of possible blocks.
     *
     * For better performance I don't want to cut string and do any operations on it and decide to create a map
     * which script has to follow to find each block. I've also added `default` option so if two blocks start with the same char (`=` as
     * to set variable and `=>` as to create arrow function) you can actually check if script will follow different path or if its alread
     * at the end of it.
     * @var array
     */
    protected $blocksMap = [
        'f' => [
            'u' => [
                'n' => [
                    'c' => [
                        't' => [
                            'i' => [
                                'o' => [
                                    'n' => [
                                        ' '  => 'FunctionBlock',
                                        "\n" => "FunctionBlock",
                                        "\r" => "FunctionBlock",
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        '=' => [
            '>'       => 'ArrowFunctionBlock',
            'default' => 'AttributeBlock'
        ],
        'l' => [
            'e' => [
                't' => [
                    ' '  => 'VariableBlock',
                    "\n" => "VariableBlock",
                    "\r" => "VariableBlock",
                ]
            ]
        ],
        'c' => [
            'o' => [
                'n' => [
                    's' => [
                        't' => [
                            ' '  => 'VariableBlock',
                            "\n" => "VariableBlock",
                            "\r" => "VariableBlock",
                        ]
                    ]
                ]
            ],
            'l' => [
                'a' => [
                    's' => [
                        's' => [
                            ' '  => 'ClassBlock',
                            "\n" => "ClassBlock",
                            "\r" => "ClassBlock",
                        ]
                    ]
                ]
            ]
        ],
        'v' => [
            'a' => [
                'r' => [
                    ' '  => 'VariableBlock',
                    "\n" => "VariableBlock",
                    "\r" => "VariableBlock",
                ],
            ]
        ],
        '.' => 'ChainLinkBlock',
        '(' => 'CallerBlock',
        'n' => [
            'e' => [
                'w' => [
                    ' '  => 'NewClassBlock',
                    "\n" => "NewClassBlock",
                    "\r" => "NewClassBlock",
                ]
            ]
        ],
        '[' => 'decideArrayBlockType'
    ];

    protected array $special = [
        "(" => true,
        ")" => true,
        "{" => true,
        "}" => true,
        "+" => true,
        "-" => true,
        "/" => true,
        "*" => true,
        "=" => true,
        "!" => true,
        '[' => true,
        ']' => true,
        '%' => true,
        '^' => true,
        ":" => true,
        ">" => true,
        "<" => true,
        "," => true,
        ' ' => true,
        "\n" => true,
        "\r" => true,
        '|' => true,
        '&' => true,
        '?' => true,
        ';' => true,
        '.' => true
    ];

    protected array $classBlocksMap = [
        '(' => "ClassMethodBlock",
    ];

    public function __construct(
        protected int $start = 0,
        string $subtype = '',
        protected array $data  = []
    ) {
        $this->setSubtype($subtype);
        $this->objectify($start);
    }

    public function getContent(): string
    {
        return self::$content;
    }

    public function setContent(string $content): self
    {
        self::$content = $content;
        return $this;
    }

    public function getCaret(): int
    {
        return $this->caret;
    }

    public function setCaret(int $caret): self
    {
        $this->caret = $caret;
        return $this;
    }

    public function getSubtype(): string
    {
        return $this->subtype;
    }

    public function setSubtype(string $subtype): self
    {
        $this->subtype = trim($subtype);
        return $this;
    }

    public function getBlocks(): array
    {
        return $this->blocks;
    }

    public function setBlocks(array $blocks): self
    {
        $this->blocks = $blocks;
        return $this;
    }

    public function aliasExists(string $name): bool
    {
        return (bool) (self::$mappedAliases[$name] ?? false);
    }

    public function getAlias(string $name): string
    {
        return self::$mappedAliases[$name] ?? $name;
    }

    public function setAlias(string $name, string $alias): self
    {
        self::$mappedAliases[$name] = $alias;
        return $this;
    }

    public function getInstruction(): string
    {
        return $this->instruction;
    }

    public function setInstruction(string $instruction): self
    {
        $this->instruction = trim(preg_replace('!\s+!', ' ', $instruction));
        return $this;
    }

    public function setInstructionStart(int $start): self
    {
        $this->instructionStart = $start;
        return $this;
    }

    public function getInstructionStart(): int
    {
        return $this->instructionStart;
    }

    public function setName(string $name): self
    {
        $this->name = trim($name);
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    protected function isEndChar(string $letter): bool
    {
        return $this->endChars[$letter] ?? false;
    }

    protected function getDefaultMap(): array
    {
        $blocksMap = $this->blocksMap;
        if ($this instanceof Block\ClassBlock) {
            $blocksMap = array_merge($blocksMap, $this->classBlocksMap);
        }
        return $blocksMap;
    }

    protected function journeyForBlockClassName(string $name, string &$mappedWord, int &$i, ?array $blocksMap = null): string | array | null
    {
        if (\is_null($blocksMap)) {
            $blocksMap = $this->getDefaultMap();
        }

        if (isset($blocksMap[$name])) {
            return $this->checkMapResult($blocksMap[$name], $i);
        }

        if (isset($blocksMap['default'])) {
            $mappedWord = substr($mappedWord, 0, -1);
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

    protected function blockFactory(string $hint, string $className, int $start, string &$possibleUndefined): Block
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
            $blocks = $this->getBlocks();
            $lastBlock = $blocks[\sizeof($blocks) - 1] ?? null;
            if (
                !($lastBlock instanceof Block\ChainLinkBlock)
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
                if ($this->isValidUndefined($possibleUndefined)) {
                    $this->blocks[] = new Block\UndefinedBlock($start - \mb_strlen($possibleUndefined), $possibleUndefined);
                }

                $this->blocks[] = $block;
                $possibleUndefined = '';
            }
            return new $class($start + 1, $hint);
        }
        return new $class($start, $hint);
    }

    protected function isValidVariable(string $variable): bool
    {
        $regex = '/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x200C\x200D]*+$/';
        $res = preg_match($regex, $variable);
        if (!$res) {
            return false;
        }

        $notAllowedConsts = [
            'break' => true, 'do' => true, 'instanceof' => true,
            'typeof' => true, 'case' => true, 'else' => true, 'new' => true,
            'var' => true, 'catch' => true, 'finally' => true, 'return' => true,
            'void' => true, 'continue' => true, 'for' => true, 'switch' => true,
            'while' => true, 'debugger' => true, 'function' => true, 'this' => true,
            'with' => true, 'default' => true, 'if' => true, 'throw' => true,
            'delete' => true, 'in' => true, 'try' => true, 'class' => true,
            'enum' => true, 'extends' => true, 'super' => true, 'const' => true,
            'export' => true, 'import' => true, 'implements' => true, 'let' => true,
            'private' => true, 'public' => true, 'yield' => true, 'interface' => true,
            'package' => true, 'protected' => true, 'static' => true, 'null' => true,
            'true' => true, 'false' => true
        ];
        return !isset($notAllowedConsts[$variable]);
    }

    protected function isWhitespace(string $letter): bool
    {
        return ctype_space($letter);
    }

    protected function findInstructionEnd(int $start, string $name, ?array $endChars = null): void
    {
        if (\is_null($endChars)) {
            $endChars = $this->endChars;
        }

        $properEnd = null;
        for ($i=$start; $i < strlen(self::$content); $i++) {
            $letter = self::$content[$i];
            Log::log("Letter: " . $letter . " end chars: " . implode(', ', array_keys($endChars)), 2);
            if ($endChars[$letter] ?? false) {
                Log::log("Proper end : " . $i, 2);
                $properEnd = $i + 1;
                $this->setCaret($properEnd);
                break;
            }
        }

        if (is_null($properEnd)) {
            throw new Exception('Proper End not found', 404);
        }

        $properStart = $start - strlen($name);
        $instruction = trim(substr(self::$content, $properStart, $properEnd - $properStart));
        $this->setInstructionStart($properStart)
            ->setInstruction($instruction);
    }

    protected function findInstructionStart(int $end, ?array $endChars = null): void
    {
        if (\is_null($endChars)) {
            $endChars = $this->endChars;
        }

        $properStart = null;
        Log::log("Find start of instruction. End: " . $end, 2);
        for ($i=$end - 1; $i >= 0; $i--) {
            $letter = self::$content[$i];
            Log::log("Letter: " . $letter, 2);
            if ($endChars[$letter] ?? false) {
                Log::log("Proper start : " . $i, 2);
                $properStart = $i + 1;
                break;
            }
        }

        if (is_null($properStart)) {
            throw new Exception('Proper Start not found', 404);
        }

        $instruction = trim(substr(self::$content, $properStart, $end - $properStart));
        $this->setInstructionStart($properStart)
            ->setInstruction($instruction);
    }

    protected function constructBlock(string $mappedWord, string $className, int &$i, string &$possibleUndefined): ?Block
    {
        Log::increaseIndent();
        Log::log("New block: " . $className . " Mapped by: " . $mappedWord, 1);

        $block = $this->blockFactory($mappedWord, $className, $i, $possibleUndefined);

        Log::log('Iteration count changed from ' . $i . " to " . $block->getCaret(), 1);
        Log::log("Instruction: `". $block->getInstruction() . "`", 1);

        $i = $block->getCaret();
        Log::decreaseIndent();
        return $block;
    }

    protected function isValidUndefined(string $undefined): bool
    {
        $undefinedEnds = ["\n" => true, ";" => true];
        return \mb_strlen($undefined) > 0 && !$this->isWhitespace($undefined) && $undefined != '}' && !isset($undefinedEnds[$undefined]);
    }

    protected function createSubBlocks(?int $start = null, ?string $value = null): void
    {
        if (is_null($value)) {
            $value = self::$content;
        }
        if (is_null($start)) {
            $start = $this->getCaret();
        }
        $map = null;
        $mappedWord = '';
        $possibleUndefined = '';
        $undefinedEnds = ["\n" => true, ";" => true];
        for ($i=$start; $i < \strlen($value); $i++) {
            $letter = self::$content[$i];
            if (
                ($startsTemplate = $this->isTemplateLiteralLandmark($letter, ''))
                || $this->isStringLandmark($letter, '')
            ) {
                $oldPos = $i;

                $i = $this->skipString($i + 1, self::$content, $startsTemplate);
                $possibleUndefined .= \mb_substr($value, $oldPos, $i - $oldPos);
                Log::log('Add new undefined: ' . $possibleUndefined . ", starPos " . $oldPos . ", new Pos:" . $i . " length: " . $i - $oldPos, 3);

                if ($this->isValidUndefined($possibleUndefined)) {
                    $this->blocks[] = new Block\UndefinedBlock($oldPos - \mb_strlen($possibleUndefined), $possibleUndefined);
                }

                $letter = self::$content[$i];
                $mappedWord = '';
                $possibleUndefined = '';
            }

            $mappedWord .= $letter;
            $possibleUndefined .= $letter;
            Log::log("Letter: " . $letter . " Mapped Word: " . $mappedWord, 3);

            $map = $this->journeyForBlockClassName($letter, $mappedWord, $i, $map);
            if (gettype($map) == 'string') {
                $oldPos = $i;
                $block = $this->constructBlock($mappedWord, $map, $i, $possibleUndefined);
                $startOfBlocksInstruction = $block->getInstructionStart();
                $oldPos; // Pos before block
                $mappedWordLen = \mb_strlen($mappedWord);
                if ($oldPos - $mappedWordLen < $startOfBlocksInstruction) {
                    $possibleUndefined = \mb_substr($possibleUndefined, 0, -$mappedWordLen);
                } else {
                    $possibleUndefined = \mb_substr($possibleUndefined, 0, -($oldPos - ($startOfBlocksInstruction - 1)));
                }
                if ($this->isValidUndefined($possibleUndefined)) {
                    $this->blocks[] = new Block\UndefinedBlock($oldPos - \mb_strlen($possibleUndefined), $possibleUndefined);
                }

                $possibleUndefined = '';
                $this->blocks[] = $block;
                $mappedWord = '';
                $map = null;
                continue;
            } elseif (\is_null($map)) {
                $mappedWord = '';
            }

            if ($this->endChars[$letter] ?? false) {
                $possibleUndefined = substr($possibleUndefined, 0, -1);
                if (
                    $this->isValidUndefined($possibleUndefined)
                    // && !($this instanceof Block\AttributeBlock)
                ) {
                    Log::log("Add undefined: " . $possibleUndefined, 3);
                    $this->blocks[] = new Block\UndefinedBlock($i - \mb_strlen($possibleUndefined), $possibleUndefined);
                }
                break;
            }

            if ($undefinedEnds[$letter] ?? false) {
                Log::log("Undefined check for: " . $possibleUndefined, 3);
                if ($this->isValidUndefined($possibleUndefined)) {
                    Log::log("Add undefined: " . $possibleUndefined, 3);
                    $this->blocks[] = new Block\UndefinedBlock($i - \mb_strlen($possibleUndefined), $possibleUndefined);
                }
                $possibleUndefined = '';
            }
        }

        $this->setCaret($i);
        Log::log("Updated caret " . $this->getCaret(),1);

    }

    protected function findAndSetName(string $prefix, array $ends): void
    {
        $instr = $this->getInstruction();
        $start = \strlen($prefix) - 1;
        if ($start < 0) {
            $start = 0;
        }
        Log::log('Start name search: ' . $instr . ", " . $start, 3);
        Log::increaseIndent();
        for ($i=$start; $i < strlen($instr); $i++) {
            $letter = $instr[$i];
            Log::log('Letter: ' . $letter, 3);
            if ($ends[$letter] ?? false) {
                $this->setName(substr($instr, $start, $i - $start));
                Log::log('Blocks name: ' . $this->getName(), 1);
                Log::decreaseIndent();
                return;
            }
        }
        Log::decreaseIndent();
        throw new Exception('Blocks name not found', 404);
    }

    protected function generateAliases(string $lastAlias = ''): void
    {
        Log::log('Start generating aliases. Last Alias: ' . $lastAlias, 2);
        // Firstly set aliases to all blocks on this level
        Log::increaseIndent();
        foreach ($this->blocks as $block) {
            Log::log('===========', 3);
            Log::log('Block: ' . $block->getName(), 3);
            if ($this->aliasExists($block->getName())) {
                Log::log('Alias for this block exists.', 2);
                continue;
            }

            $alias = $this->generateAlias($block->getName(), $lastAlias);
            Log::log('Generated Alias:' . $alias . ", previous alias: " . $lastAlias, 1);

            if (\mb_strlen($alias) == 0) {
                Log::log('skip alias.', 3);
                continue;
            }

            Log::log('Set generated alias.', 3);
            $this->setAlias($block->getName(), $alias);
            $lastAlias = $alias;
        }
        Log::decreaseIndent();

        if ($this instanceof MethodBlock && !($this instanceof Block\NewClassBlock)) {
            Log::log('Block is an method.', 2);
            Log::increaseIndent();
            foreach ($this->getArguments() as $arg) {
                Log::log('Argument: ' . $arg, 3);
                if ($this->aliasExists($arg)) {
                    Log::log('Argument already exists with this name.', 2);
                    continue;
                }
                $alias = $this->generateAlias($arg, $lastAlias);
                Log::log('Generated alias: ' . $alias . ", last alias: " . $lastAlias, 1);
                $this->setAlias($arg, $alias);
                $lastAlias = $alias;
            }
            Log::decreaseIndent();
        }

        // Then to level below
        Log::increaseIndent();
        foreach ($this->blocks as $block) {
            $block->generateAliases($lastAlias);
        }
        Log::decreaseIndent();

    }

    protected function generateAlias(string $name, string $lastAlias): string
    {
        Log::log("Generate alias for " . $name, 1);
        if (\mb_strlen($name) == 0) {
            Log::log("Name is empty, skipping...", 1);
            return '';
        }
        Log::log("Last alias: " . $lastAlias, 1);
        if (\mb_strlen($lastAlias) != 0) {
            $lastLetter = \mb_substr($lastAlias, -1);
        } else {
            $lastLetter = 'df';
        }
        Log::log("Last letter: " . $lastLetter, 1);

        if ($newAliasSufix = $this->aliasMap[$lastLetter]) {
            Log::log("Next sufix found: " . $newAliasSufix, 1);
            $newAlias = \mb_substr($lastAlias ?? '', 0, \mb_strlen($lastAlias ?? '') - 1) . $newAliasSufix;
        } else {
            Log::log("Next sufix not found, adding another letter: " . $this->aliasMap['df'], 1);
            $newAlias = ($lastAlias ?? '') . $this->aliasMap['df'];
        }
        Log::log("New alias: " . $newAlias, 1);
        if ($this->isValidVariable($newAlias)) {
            return $newAlias;
        }
        Log::log("Alias is not valid, generating new one...", 1);
        return $this->generateAlias($newAlias, $newAlias);
    }

    protected function replaceVariablesWithAliases(string $value): string
    {
        $word = '';
        $minifiedValue = '';
        $stringInProgress = false;
        $templateVarInProgress = false;
        $templateLiteralInProgress = false;
        Log::increaseIndent();
        for ($i=0; $i < \mb_strlen($value); $i++) {
            $letter = $value[$i];
            Log::log('Letter: ' . $letter . ", word: `" . $word . "`", 3);
            $isLiteralLandmark = $this->isTemplateLiteralLandmark($letter, $value[$i - 1] ?? null, $templateLiteralInProgress);
            if ($templateVarInProgress && !$isLiteralLandmark) {
                if ($letter == '}') {
                    Log::log('End template literal', 2);
                    $templateVarInProgress = false;
                    $alias = $this->getAlias($word);
                    $minifiedValue .= $alias . $letter;
                    Log::log('minified value:' . $minifiedValue, 2);
                    $word = '';
                    continue;
                } else {
                    $word .= $letter;
                }
                continue;
            }

            if ($isLiteralLandmark) {
                $templateLiteralInProgress = !$templateLiteralInProgress;
            } elseif ($this->isStringLandmark($letter, $value[$i - 1] ?? null, $stringInProgress)) {
                $stringInProgress = !$stringInProgress;
            }

            if (
                $templateLiteralInProgress
                && $this->startsTemplateLiteralVariable($letter, $value, $i)
            ) {
                Log::log('Start literal template', 2);
                $templateVarInProgress = true;
            }

            if ($stringInProgress || $templateLiteralInProgress) {
                $minifiedValue .= $letter;
                $word = '';
                continue;
            }

            if ($this->isSpecial($letter)) {
                Log::log('Special Char!', 2);
                $alias = $this->getAlias($word);
                $minifiedValue .= $alias . $letter;
                Log::log('minified value:' . $minifiedValue, 2);
                $word = '';
                continue;
            }

            $word .= $letter;
        }
        Log::decreaseIndent();
        $alias = $this->getAlias($word);
        Log::log('Last alias check:' . $word . " => " . $alias, 2);
        return $minifiedValue . $alias;
    }

    protected function startsTemplateLiteralVariable(string $letter, string $value, int $i): bool
    {
        return ($value[$i - 1] ?? '') . $letter == '${'
            && ($value[$i - 2] ?? '') . ($value[$i - 1] ?? '') . $letter != '\${';
    }

    protected function isTemplateLiteralLandmark(string $letter, string $previousLetter, bool $inString = false): bool
    {
        return $letter === '`' && (
            $inString && $previousLetter !== '\\'
            || !$inString
        );
    }

    protected function isStringLandmark(string $letter, string $previousLetter, bool $inString = false): bool
    {
        return ($letter === '"' || $letter === "'")
            && (
                !$inString
                || $inString && $previousLetter !== '\\'
            );
    }

    public function skipString(int $start, string $value, bool $isTemplate = false): int
    {
        for ($i=$start; $i < \mb_strlen($value); $i++) {
            $letter = $value[$i];
            if ($isTemplate && $this->isTemplateLiteralLandmark($letter, $value[$i - 1] ?? '', true)) {
                return $i + 1;
            } elseif (!$isTemplate && $this->isStringLandmark($letter, $value[$i - 1] ?? '', true)) {
                return $i + 1;
            }
        }
        return $i;
    }

    protected function isSpecial(string $letter): bool
    {
        return $this->special[$letter] ?? false;
    }

    protected function removeAdditionalSpaces(string $instruction): string
    {
        $properInstr = '';
        for ($i=0; $i < \mb_strlen($instruction); $i++) {
            $letter = $instruction[$i];
            if (
                $this->isWhitespace($letter) && $this->isSpecial($instruction[$i + 1])
                || $this->isWhitespace($letter) && $this->isWhitespace($instruction[$i + 1])
                || $this->isWhitespace($letter) && $this->isSpecial($instruction[$i - 1])
            ) {
                continue;
            }
            $properInstr .= $letter;
        }
        return $properInstr;
    }

    protected function decideArrayBlockType(int $start) {
        for ($i=$start - 1; $i >= 0; $i--) {
            $letter = self::$content[$i];
            if ($this->isWhitespace($letter)) {
                continue;
            }
            if (!$this->isSpecial($letter)) {
                return 'BracketChainLinkBlock';
            }
            return "ArrayBlock";
        }
        throw new Exception("Couldn't decide how found bracket was used", 400);
    }
}
