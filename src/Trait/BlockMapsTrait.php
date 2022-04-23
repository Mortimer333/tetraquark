<?php declare(strict_types=1);

namespace Tetraquark\Trait;

use \Tetraquark\{Block as Block};

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
        '{' => 'ScopeBlock',
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
        "e" => [
            "l" => [
                "s" => [
                    "e" => [
                        "{" => "ElseBlock",
                        ' '  => 'ElseBlock',
                        "\n" => "ElseBlock",
                        "\r" => "ElseBlock",
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

        if ($this instanceof MethodBlock) {
            $blocksMap = array_merge($blocksMap, $this->callerBlocksMap);
            if ($this->getStatus() === $this::CREATING_ARGUMENTS) {
                $blocksMap = array_merge($blocksMap, $this->callerArgsBlocksMap);
            }
        } elseif ($this instanceof VariableBlock) {
            $blocksMap = array_merge($blocksMap, $this->variableBlocksMap);
        }

        return $blocksMap;
    }
}
