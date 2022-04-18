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
    /** @var int Queue indicator */
    protected int    $childIndex;
    /** @var Block Parent of this block */
    protected Block  $parent;
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
            'o' => [
                'r' => [
                    ' '  => 'ForBlock',
                    "\n" => "ForBlock",
                    "\r" => "ForBlock",
                    '('  => "ForBlock",
                ]
            ],
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
            'default' => 'AttributeBlock',
            '='       => [
                "="       => "TripleEqualBlock",
                "default" => "DoubleEqualBlock",
            ]
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
        '.' => [
            '.' => [
                '.' => false
            ],
            'default' => 'ChainLinkBlock'
        ],
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
        '[' => 'decideArrayBlockType',
        '{' => 'ObjectBlock',
        'i' => [
            'f' => [
                ' '  => "IfBlock",
                "\n" => "IfBlock",
                "\r" => "IfBlock",
                "("  => "IfBlock",
            ]
        ],
        "w" => [
            "h" => [
                "i" => [
                    "l" => [
                        "e" => [
                            ' '  => 'WhileBlock',
                            "\n" => "WhileBlock",
                            "\r" => "WhileBlock",
                            '('  => "WhileBlock",
                        ]
                    ]
                ]
            ]
        ],
        "s" => [
            "w" => [
                "i" => [
                    "t" => [
                        "c" => [
                            "h" => [
                                ' '  => 'SwitchBlock',
                                "\n" => "SwitchBlock",
                                "\r" => "SwitchBlock",
                                '('  => "SwitchBlock",
                            ]
                        ]
                    ]
                ]
            ]
        ],
        "d" => [
            "o" => [
                ' '  => 'DoWhileBlock',
                "\n" => "DoWhileBlock",
                "\r" => "DoWhileBlock",
                '{'  => "DoWhileBlock",
            ]
        ],
        "/" => [
            "/" => "SingleCommentBlock",
            "*" => "MultiCommentBlock",
        ],
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

    protected array $objectBlocksMap = [
        ':' => "ObjectValueBlock",
        ',' => "ObjectSoloValueBlock",
        '.' => [
            "." => [
                "." => "SpreadBlock"
            ],
            "default" => 'ChainLinkBlock'
        ],
    ];

    protected array $callerBlocksMap = [
        '.' => [
            "." => [
                "." => "SpreadBlock"
            ],
            "default" => 'ChainLinkBlock'
        ],
    ];

    protected array $arrayBlocksMap = [
        '.' => [
            "." => [
                "." => "SpreadBlock"
            ],
            "default" => 'ChainLinkBlock'
        ],
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
        // $this->instruction = trim(preg_replace('!\s+!', ' ', $instruction));
        $this->instruction = $instruction;
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

        $additionalPaths = [
            Block\ClassBlock ::class => $this->classBlocksMap,
            Block\ObjectBlock::class => $this->objectBlocksMap,
            Block\ArrayBlock ::class => $this->arrayBlocksMap,
        ];

        $blocksMap = array_merge($blocksMap, $additionalPaths[$this::class] ?? []);

        if ($this instanceof MethodBlock) {
            $blocksMap = array_merge($blocksMap, $this->callerBlocksMap);
        }

        return $blocksMap;
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

    protected function blockFactory(string $hint, string $className, int $start, string &$possibleUndefined, array &$blocks): Block
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
            // $allBlocks = $this->getBlocks();
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
                    Log::log("Valid undefined5: " . $possibleUndefined);
                    $blocks[] = new Block\UndefinedBlock($start - \mb_strlen($possibleUndefined), $possibleUndefined);
                }

                $blocks[] = $block;
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

            if (
                ($startsTemplate = $this->isTemplateLiteralLandmark($letter, ''))
                || $this->isStringLandmark($letter, '')
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
                ($startsTemplate = $this->isTemplateLiteralLandmark($letter, ''))
                || $this->isStringLandmark($letter, '')
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

    protected function constructBlock(string $mappedWord, string $className, int &$i, string &$possibleUndefined, array &$blocks): ?Block
    {
        $block = $this->blockFactory($mappedWord, $className, $i, $possibleUndefined, $blocks);
        $i = $block->getCaret();
        return $block;
    }

    protected function isValidUndefined(string $undefined): bool
    {
        $undefinedEnds = ["\n" => true, ";" => true, "}" => true];
        $undefined = trim($undefined);
        return \mb_strlen($undefined) > 0 && !$this->isWhitespace($undefined) && !isset($undefinedEnds[$undefined]);
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
                ($startsTemplate = $this->isTemplateLiteralLandmark($letter, ''))
                || $this->isStringLandmark($letter, '')
            ) {
                $oldPos = $i;
                $i = $this->skipString($i + 1, self::$content, $startsTemplate);

                if ($this->isValidUndefined($possibleUndefined)) {
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
                $lenOfUndefined = $instStart - $oldPos;
                if ($lenOfUndefined - 1 > 0) {
                    $possibleUndefined = \mb_substr($possibleUndefined, 0, $lenOfUndefined - 1);
                } else {
                    $possibleUndefined = '';
                }

                if ($this->isValidUndefined($possibleUndefined)) {
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
                if ($this->isValidUndefined($possibleUndefined)) {
                    $blocks[] = new Block\UndefinedBlock($i - \mb_strlen($possibleUndefined), $possibleUndefined);
                    $possibleUndefined = '';
                }
                break;
            }
        }

        if ($this->isValidUndefined($possibleUndefined)) {
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
        if ($this->isValidVariable($newAlias)) {
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
            $isLiteralLandmark = $this->isTemplateLiteralLandmark($letter, $value[$i - 1] ?? null, $templateLiteralInProgress);
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
            } elseif ($this->isStringLandmark($letter, $value[$i - 1] ?? null, $stringInProgress)) {
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

            if ($this->isSpecial($letter)) {
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

    protected function isTemplateLiteralLandmark(string $letter, string $previousLetter, bool $inString = false): bool
    {
        return $letter === '`' && (
            $inString && $previousLetter !== '\\'
            || !$inString
        );
    }

    protected function isString(string $letter): bool
    {
        $strings = [
            '"' => true,
            "'" => true,
            '`' => true,
        ];
        return $strings[$letter] ?? false;
    }

    protected function isStringLandmark(string $letter, string $previousLetter, bool $inString = false): bool
    {
        return ($letter === '"' || $letter === "'")
            && (
                !$inString
                || $inString && $previousLetter !== '\\'
            );
    }

    public function skipString(int $start, string $value, bool $isTemplate = false, bool $reverse = false): int
    {
        $modifier = (((int)!$reverse) * 2) - 1;

        for ($i=$start; !$reverse && $i < \mb_strlen($value) || $reverse && $i >= 0; $i += $modifier) {
            $letter = $value[$i];
            if ($isTemplate && $this->isTemplateLiteralLandmark($letter, $value[$i - 1] ?? '', true)) {
                return $i + $modifier;
            } elseif (!$isTemplate && $this->isStringLandmark($letter, $value[$i - 1] ?? '', true)) {
                return $i + $modifier;
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
                $this->isWhitespace($letter) && $this->isSpecial($instruction[$i + 1] ?? '')
                || $this->isWhitespace($letter) && $this->isWhitespace($instruction[$i + 1] ?? '')
                || $this->isWhitespace($letter) && $this->isSpecial($instruction[$i - 1] ?? '')
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
            if ($this->isWhitespace($letter)) {
                continue;
            }
            if (!$this->isSpecial($letter)) {
                return 'BracketChainLinkBlock';
            }
            return "ArrayBlock";
        }
        return "ArrayBlock";
    }

    protected function canBeAliased(string $name, Block $block): bool
    {
        $reserved = [];
        if ($block instanceof Block\ClassMethodBlock) {
            $reserved['constructor'] = false;
        }

        return $reserved[$name] ?? true;
    }

    public function displayBlocks(array $blocks)
    {
        foreach ($blocks as $block) {
            Log::log("Block: " . get_class($block));
            Log::log("Subtype: " . $block->getSubtype());
            Log::log("Instruction: " . $block->getInstruction());
            Log::log("Instruction Start: " . $block->getInstructionStart());
            Log::log("Name: `" . $block->getName() . "`");
            if (method_exists($block, 'getValue')) {
                Log::log("Value: `" . $block->getValue() . "`");
            }
            if (method_exists($block, 'getArguments')) {
                Log::log("Arguments: [" . \sizeof($block->getArguments()) . "] `");
                Log::increaseIndent();
                foreach ($block->getArguments() as $argument) {
                    $this->displayBlocks($argument);
                }
                Log::decreaseIndent();
            }
            if (method_exists($block, 'getCondBlocks')) {
                Log::increaseIndent();
                $this->displayBlocks($block->getCondBlocks());
                Log::decreaseIndent();
            }
            Log::log("Alias: `" . $block->getAlias($block->getName()) . "`");
            Log::log("=======");
            Log::increaseIndent();
            $this->displayBlocks($block->blocks);
            Log::decreaseIndent();
        }
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

    public function setChildIndex(int $childIndex): void
    {
        $this->childIndex = $childIndex;
    }

    public function getChildIndex(): int
    {
        return $this->childIndex;
    }

    public function setParent(Block $parent): void
    {
        $this->parent = $parent;
    }

    public function getParent(): Block
    {
        return $this->parent;
    }
}
