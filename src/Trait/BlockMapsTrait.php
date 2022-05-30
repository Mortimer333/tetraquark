<?php declare(strict_types=1);

namespace Tetraquark\Trait;

use \Tetraquark\{Block, Log, Validate, Foundation};

trait BlockMapsTrait
{
    protected array $newConditionAppearers = [' ', "\n", ';', '}', '{'];

    /**
     * Map of possible blocks prefixed with whitespace.
     *
     * For better performance I don't want to cut string and do any operations on it and decide to create a map
     * which script has to follow to find each block. I've also added `default` option so if two blocks start with the same char (`=` as
     * to set variable and `=>` as to create arrow function) you can actually check if script will follow different path or if its alread
     * at the end of it.
     * @var array
     */
    protected array $blocksMapPrefix = [
        'f' => [
            'o' => [
                'r' => [
                    ' '  => 'ForBlock',
                    "\n" => "ForBlock",
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
                                        "("  => "FunctionBlock",
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        'l' => [
            'e' => [
                't' => [
                    ' '  => 'VariableBlock',
                    "\n" => "VariableBlock",
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
                ],
            ]
        ],
        'n' => [
            'e' => [
                'w' => [
                    ' '  => 'NewClassBlock',
                    "\n" => "NewClassBlock",
                ]
            ]
        ],
        'i' => [
            'f' => [
                ' '  => "IfBlock",
                "\n" => "IfBlock",
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
                '{'  => "DoWhileBlock",
            ]
        ],
        "e" => [
            "l" => [
                "s" => [
                    "e" => [
                        "{" => "ElseBlock",
                        ' '  => 'ElseBlock',
                        "\n" => "ElseBlock",
                    ]
                ]
            ],
            "x" => [
                "p" => [
                    "o" => [
                        "r" => [
                            "t" => [
                                "{" => "ExportBlock",
                                ' '  => 'ExportBlock',
                                "\n" => "ExportBlock",
                            ]
                        ]
                    ]
                ]
            ]
        ],
        "r" => [
            "e" => [
                "t" => [
                    "u" => [
                        "r" => [
                            "n" => [
                                ' '  => 'ReturnBlock',
                                "\n" => "ReturnBlock",
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];

    protected array $blocksMapNoPrefix = [
        '=' => [
            '>'       => 'ArrowFunctionBlock',
            'default' => 'AttributeBlock',
            '='       => [
                "="       => "TripleEqualBlock",
                "default" => "DoubleEqualBlock",
            ]
        ],
        '.' => [
            '.' => [
                '.' => false
            ],
            'default' => 'ChainLinkBlock'
        ],
        '(' => 'CallerBlock',
        '[' => 'decideArrayBlockType',
        '{' => 'ScopeBlock',
        '}' => [
            "e" => [
                "l" => [
                    "s" => [
                        "e" => [
                            "{" => "ElseBlock",
                            ' '  => 'ElseBlock',
                            "\n" => "ElseBlock",
                        ]
                    ]
                ]
            ]
        ],
        "-" => [
            "-"       => "OperatorBlock",
            "="       => "AttributeBlock",
            "default" => "SymbolBlock",
        ],
        "+" => [
            "+"       => "OperatorBlock",
            "="       => "AttributeBlock",
            "default" => "SymbolBlock",
        ],
        "*" => [
            "*" => [
                "="       => "AttributeBlock",
                "default" => "SymbolBlock",
            ],
            "="       => "AttributeBlock",
            "default" => "SymbolBlock",
        ],
        "/" => [
            "default" => "SymbolBlock",
            "="       => "AttributeBlock",
            "/"       => "SingleCommentBlock",
            "*"       => "MultiCommentBlock",
        ],
        "%" => [
            "="       => "AttributeBlock",
            "default" => "SymbolBlock",
        ],
        "^" => [
            "="       => "AttributeBlock",
            "default" => "SymbolBlock",
        ],
        "|" => [
            "|" => [
                "="       => "AttributeBlock",
                "default" => "SymbolBlock",
            ],
            "="       => "AttributeBlock",
            "default" => "SymbolBlock",
        ],
        "&" => [
            "&" => [
                "="       => "AttributeBlock",
                "default" => "SymbolBlock",
            ],
            "="       => "AttributeBlock",
            "default" => "SymbolBlock",
        ],
        ">" => [
            "=" => [
                "="       => "SymbolBlock",
                "default" => "SymbolBlock"
            ],
            ">" => [
                ">" => [
                    "="       => "AttributeBlock",
                    "default" => "SymbolBlock",
                ],
                "="       => "AttributeBlock",
                "default" => "SymbolBlock",
            ],
            "default" => "SymbolBlock",
        ],
        "<" => [
            "=" => [
                "="       => "SymbolBlock",
                "default" => "SymbolBlock"
            ],
            "<" => [
                "="       => "AttributeBlock",
                "default" => "SymbolBlock",
            ],
            "default" => "SymbolBlock",
        ],
        "?" => [
            "?" => [
                "="       => "AttributeBlock",
                "default" => "SymbolBlock",
            ],
            "default" => "SymbolBlock",
        ],
        "!" => [
            "=" => [
                "=" => "SymbolBlock",
                "default" => "SymbolBlock",
            ],
            "default" => "SymbolBlock",
        ],
        ":" => "SymbolBlock",
    ];

    protected array $classBlocksMap  = [
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
        "{" => "ObjectBlock",
    ];
    protected array $objectValueBlocksMap = [
        '.' => [
            "." => [
                "." => "SpreadBlock"
            ],
            "default" => 'ChainLinkBlock'
        ],
        "{" => "ObjectBlock",
    ];
    protected array $callerBlocksMap = [
        '.' => [
            "." => [
                "." => "SpreadBlock"
            ],
            "default" => 'ChainLinkBlock'
        ],
        "{" => "ObjectBlock",
    ];
    protected array $arrayBlocksMap  = [
        '.' => [
            "." => [
                "." => "SpreadBlock"
            ],
            "default" => 'ChainLinkBlock'
        ],
        ',' => "ArrayItemSeperatorBlock",
        "{" => "ObjectBlock",
    ];
    protected array $variableBlocksMap = [
        "{" => "ObjectBlock",
    ];
    protected array $callerArgsBlocksMap = [
        "{" => "ObjectBlock",
    ];
    protected array $returnBlocksMap = [
        "{" => "ObjectBlock",
    ];
    protected array $chainLinkBlocksMap = [
        "{" => "ObjectBlock",
    ];
    protected array $exportBlocksMap = [
        "{" => "ExportObjectBlock",
        "*" => [
            "default" => "ExportAllBlock"
        ],
        "&_" => [
            "d" => [
                "e" => [
                    "f" => [
                        "a" => [
                            "u" => [
                                "l" => [
                                    "t" => [
                                        ' '  => 'ExportDefaultBlock',
                                        "\n" => "ExportDefaultBlock",
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            "f" => [
                "r" => [
                    "o" => [
                        "m" => [
                            ' '  => 'ExportFromBlock',
                            "\n" => "ExportFromBlock",
                            "'"  => "ExportFromBlock",
                            '"'  => "ExportFromBlock",
                            "`"  => "ExportFromBlock",
                        ]
                    ]
                ]
            ]
        ]
    ];
    protected array $exportObjectBlocksMap = [
        "," => "ExportObjectItemBlock",
    ];

    protected function getDefaultMap(): array
    {
        $blocksMap = $this->blocksMapNoPrefix;
        foreach ($this->newConditionAppearers as $value) {
            $oldValue = $blocksMap[$value] ?? null;
            $blocksMap[$value] = $this->blocksMapPrefix;
            if (!\is_null($oldValue)) {
                $blocksMap[$value]['default'] = $oldValue;
            }
        }

        $additionalPaths = [
            Block\ArrayBlock           ::class => $this->arrayBlocksMap,
            Block\BracketChainLinkBlock::class => $this->chainLinkBlocksMap,
            Block\ChainLinkBlock       ::class => $this->chainLinkBlocksMap,
            Block\ClassBlock           ::class => $this->classBlocksMap,
            Block\ObjectBlock          ::class => $this->objectBlocksMap,
            Block\ObjectValueBlock     ::class => $this->objectValueBlocksMap,
            Block\ReturnBlock          ::class => $this->returnBlocksMap,
            Block\ExportBlock          ::class => $this->exportBlocksMap,
        ];

        $blocksMap = $this->mergeBlockMaps($blocksMap, $additionalPaths[$this::class] ?? []);

        if ($this instanceof Block\MethodBlock || $this instanceof Block\CallerBlock) {
            $blocksMap = $this->mergeBlockMaps($blocksMap, $this->callerBlocksMap);
            if ($this instanceof Block\MethodBlock && $this->getStatus() === $this::CREATING_ARGUMENTS) {
                $blocksMap = $this->mergeBlockMaps($blocksMap, $this->callerArgsBlocksMap);
            }
        } elseif ($this instanceof Foundation\VariableBlockAbstract && !$this instanceof Block\ExportBlock) {
            $blocksMap = $this->mergeBlockMaps($blocksMap, $this->variableBlocksMap);
        } elseif ($this instanceof Block\ObjectBlock || $this instanceof Block\ClassBlock) {
            // Here we remove all directions to any Block which isn't special symbol.
            // Obj names are free game, they can be `for` or `let` and that will break all our journey search so we have to remove it.
            foreach ($blocksMap as $key => $value) {
                if (!Validate::isSpecial($key)) {
                    if (isset($blocksMap[$key]['default'])) {
                        unset($blocksMap[$key]['default']);
                    } else {
                        unset($blocksMap[$key]);
                    }
                }
            }

            foreach ($this->newConditionAppearers as $value) {
                if (is_array($blocksMap[$value])) {
                    foreach ($blocksMap[$value] as $key => $prop) {
                        if (!Validate::isSpecial($key) && $key != 'default') {
                            unset($blocksMap[$value][$key]);
                        }
                    }
                }
            }
        } elseif ($this instanceof Block\ExportObjectBlock) {
            $blocksMap = $this->exportObjectBlocksMap;
        }

        // if ($this instanceof Block\ExportBlock) {
        //     die(json_encode($blocksMap));
        // }

        return $blocksMap;
    }

    protected function mergeBlockMaps(array $old, array $new): array
    {
        /*
        ar = [
            '{' =>[
                'f' => ...
                'default' => ...
            ],
            ' ' =>[
                'f' => ...
                'default' => ...
            ],
        ]
         */
        $onlyConditionAppearers = [];
        if (isset($new["&_"])) {
            $onlyConditionAppearers = $new["&_"];
            unset($new["&_"]);
        }

        $old = $this->addStepsToBlockMap($old, $new);

        foreach ($this->newConditionAppearers as $value) {
            if (is_array($old[$value])) {
                $old[$value] = $this->addStepsToBlockMap($old[$value], $new);
                $old[$value] = $this->addStepsToBlockMap($old[$value], $onlyConditionAppearers);
            }
        }

        return $old;
    }

    private function addStepsToBlockMap(array $blocksMap, array $steps): array
    {
        foreach ($steps as $landmark => $step) {
            if (isset($blocksMap[$landmark]) && is_array($step)) {
                $blocksMap[$landmark] = $this->addStepsToBlockMap($blocksMap[$landmark], $step);
            } else {
                $blocksMap[$landmark] = $step;
            }
        }
        return $blocksMap;
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
}
