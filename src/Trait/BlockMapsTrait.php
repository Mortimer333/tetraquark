<?php declare(strict_types=1);

namespace Tetraquark\Trait;

use \Tetraquark\{Block, Log, Validate};

trait BlockMapsTrait
{
    /**
     * Map of possible blocks.
     *
     * For better performance I don't want to cut string and do any operations on it and decide to create a map
     * which script has to follow to find each block. I've also added `default` option so if two blocks start with the same char (`=` as
     * to set variable and `=>` as to create arrow function) you can actually check if script will follow different path or if its alread
     * at the end of it.
     * @var array
     */
    protected array $blocksMap = [
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
                ]
            ]
        ],
        '[' => 'decideArrayBlockType',
        '{' => 'ScopeBlock',
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
        "-" => [
            "="       => "AttributeBlock",
            "default" => "SymbolBlock",
        ],
        "+" => [
            "="       => "AttributeBlock",
            "default" => "SymbolBlock",
        ],
        "*" => [
            "*"       => [
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
            "="       => "AttributeBlock",
            "default" => "SymbolBlock",
        ],
        "&" => [
            "="       => "AttributeBlock",
            "default" => "SymbolBlock",
        ],
        ">" => [
            ">" => [
                ">" => "SymbolBlock",
            ],
            "default" => "SymbolBlock",
        ],
        "<" => [
            "<" => "SymbolBlock",
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
            ]
        ]
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
    protected array $callerBlocksMap = [
        '.' => [
            "." => [
                "." => "SpreadBlock"
            ],
            "default" => 'ChainLinkBlock'
        ],
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

    protected function getDefaultMap(): array
    {
        $blocksMap = $this->blocksMap;

        $additionalPaths = [
            Block\ClassBlock ::class => $this->classBlocksMap,
            Block\ObjectBlock::class => $this->objectBlocksMap,
            Block\ArrayBlock ::class => $this->arrayBlocksMap,
        ];

        $blocksMap = array_merge($blocksMap, $additionalPaths[$this::class] ?? []);

        if ($this instanceof Block\MethodBlock) {
            $blocksMap = array_merge($blocksMap, $this->callerBlocksMap);
            if ($this->getStatus() === $this::CREATING_ARGUMENTS) {
                $blocksMap = array_merge($blocksMap, $this->callerArgsBlocksMap);
            }
        } elseif ($this instanceof Block\VariableBlock) {
            $blocksMap = array_merge($blocksMap, $this->variableBlocksMap);
        } elseif ($this instanceof Block\ObjectBlock) {
            // Here we remove all directions to any Block which isn't special symbol.
            // Obj names are free game, they can be `for` or `let` and that will break all our journey search so we have to remove it.
            foreach ($blocksMap as $key => $value) {
                if (!Validate::isSpecial($key)) {
                    unset($blocksMap[$key]);
                }
            }
        }

        return $blocksMap;
    }
}
