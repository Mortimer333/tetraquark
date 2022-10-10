<?php declare(strict_types=1);

namespace Tetraquark\Analyzer\JavaScript\Util;

abstract class LandmarkStorage
{
    public const ARRAY_CHAIN_INSTRUCTION = [
        '/s|e\[/find:"]":"[":"index">read:"index"\\' => [
            "class" => "ChainBlock",
            "array" => true,
            "_block" => [
                "end" => '/varend\\',
                "include_end" => true,
            ],
            "_extend" => [
                '/s|e\=/"!=">decrease\\' => [
                    "class" => "ChainBlock",
                    "array" => true,
                    "var" => true,
                    "_block" => [
                        "end" => '/varend\\',
                        "include_end" => true,
                    ],
                ],
                '/s|e\(/find:")":"(":"values">read:"values"\\' => [
                    "class" => "ChainBlock",
                    "array" => true,
                    "method" => true,
                ]
            ]
        ]
    ];

    public static function getIfAndShortIf(): array
    {
        return [
            '/s|end\if/s|e\(/find:")":"(":"condition">read:"condition"\\' =>  [
                "_extend" => [
                    '/s|e\{' => [
                        "class" => "IfBlock",
                        "_block" => [
                            "end" => "}",
                            "nested" => "{"
                        ]
                    ],
                    /* SHORT IF */
                    '/nparenthesis>decrease\/varend\\' => [
                        "class" => "ShortIfBlock"
                    ],
                ]
            ]
        ];
    }

    public static function getClassDefinition(): array
    {
        return [
            '/s|end\class/s|e\/word:"class_name"\\' => [
                "_extend" => [
                    '/s|e\{' => [
                        "class" => "ClassBlock",
                        "_block" => [
                            "end" => "}",
                            "nested" => "{"
                        ],
                    ],
                    '/s\extends/word:"extends_name"\/s|e\{' => [
                        "class" => "ClassBlock",
                        "extends" => true,
                        "_block" => [
                            "end" => "}",
                            "nested" => "{"
                        ],
                    ]
                ]
            ]
        ];
    }

    public static function getVariableDefinitions(): array
    {
        return [
            /* LET */
            "/s|end\let/s\\" => [
                "class" => "LetVariableBlock",
                "_block" => [
                    "end" => '/varend:false\\',
                    "include_end" => true,
                ]
            ],
            /* CONST */
            "/s|end\const/s\\" => [
                "class" => "ConstVariableBlock",
                "_block" => [
                    "end" => '/varend:false\\',
                    "include_end" => true,
                ]
            ],
            /* VAR */
            '/s|end\var/s\\' => [
                "class" => "VarVariableBlock",
                "_block" => [
                    "end" => '/varend:false\\',
                    "include_end" => true,
                ]
            ],
        ];
    }

    public static function getVariable(): array
    {
        $class = "VariableInstanceBlock";
        return [
            '/s|end\/"#">isprivate|e\/word:"name"\\' => [
                "class" => $class,
                "empty" => true,
                "_extend" => [
                    '/s|e\\' => [
                        "_extend" => [
                            '=/"!=">decrease\\' => [
                                "class" => $class,
                                "replace" => true,
                                "_block" => [
                                    "end" => "/varend\\",
                                    "include_end" => true,
                                ]
                            ],
                            '/assignment\\=/"!=">decrease\\' => [
                                "class" => $class,
                                "_block" => [
                                    "end" => "/varend\\",
                                    "include_end" => true,
                                ]
                            ],
                            '++' => [
                                "class" => $class,
                                "type" => "addition",
                            ],
                            '--' => [
                                "class" => $class,
                                "type" => "subtraction",
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    public static function getStaticVariable(): array
    {
        return [
            '/s|end\static' => [
                "_extend" => [
                    /* STATIC INITIALIZATION */
                    '/s|e\{' => [
                        "class" => "StaticInitializationBlock",
                        "_block" => [
                            "end" => "}",
                            "skip" => "{"
                        ],
                    ],
                    '/s\/"#">isprivate|e\\' => [
                        "_extend" => [
                            '[/find:"]":"[":"name">read:"name"\/s|e\(/find:")":"(":"arguments">read:"arguments"\/s|e\{' => [
                                "class" => "StaticClassMethodBlock",
                                "constant_name" => true,
                                "_block" => [
                                    "end" => "}",
                                    "nested" => "{"
                                ],
                            ],
                            '/word:"name"\/s|e\\' => [
                                "class" => "StaticVariableInstanceBlock",
                                "empty" => true,
                                "_extend" => [
                                    '=/"!=">decrease\\' => [
                                        "class" => "StaticVariableInstanceBlock",
                                        "replace" => true,
                                        "_block" => [
                                            "end" => "/varend\\",
                                            "include_end" => true,
                                        ]
                                    ],
                                    '(/find:")":"(":"arguments">read:"arguments"\/s|e\{' => [
                                        "class" => "StaticClassMethodBlock",
                                        "_block" => [
                                            "end" => "}",
                                            "nested" => "{"
                                        ],
                                    ]
                                ]
                            ]
                        ]
                    ],
                ]
            ]
        ];
    }

    public static function getSpreadVariable(): array
    {
        return [
            '.../s|e\/word:"name"\\' => [
                "class" => "VariableInstanceBlock",
                "spread" => true,
            ]
        ];
    }

    public static function getArrayAndDeconstructionAssignment(): array
    {
        return [
            '[' => [
                "class" => "ArrayBlock",
                "_block" => [
                    "end" => "]",
                    "nested" => "[",
                ],
                "_extend" => [
                    '/find:"]":"[":"deconstruction">read:"deconstruction"\/s|e\=' => [
                        "class" => "DeconstructionAssignmentBlock",
                        "_block" => [
                            "end" => "/varend\\",
                            "include_end" => true,
                        ]
                    ]
                ]
            ]
        ];
    }

    public static function getSpreadArray(): array
    {
        return [
            '.../s|e\[' => [
                "class" => "ArrayBlock",
                "spread" => true,
                "_block" => [
                    "end" => "]",
                    "nested" => "[",
                ]
            ]
        ];
    }

    public static function getApostrophe(): array
    {
        return [
            "'/strend:\"'\"\\" => [
                "class" => "StringBlock",
            ]
        ];
    }


    public static function getTemplateLiteral(): array
    {
        return [
            '`/strend:"`":"template">templateliteral:"template">read:"template"\\' => [
                "class" => "StringBlock",
            ]
        ];
    }


    public static function getQuote(): array
    {
        return [
            '"/strend:\'"\'\\' => [
                "class" => "StringBlock",
            ]
        ];
    }

    public static function getComma(): array
    {
        return [
            ',' => [
                "class" => "CommaBlock",
            ]
        ];
    }

    public static function getArrowFunctionWithAsync(): array
    {
        $arrowMethodInstruction = [
            '(/find:")":"(":"condition">read:"condition"\/s|e\=>/s|e\{' => [
                "class" => "ArrowMethodBlock",
                "parenthesis" => true,
                "block" => true,
                "_block" => [
                    "end" => "}",
                    "nested" => "{"
                ]
            ],
            '/word\/s|e\=>/s|e\{' => [
                "class" => "ArrowMethodBlock",
                "parenthesis" => false,
                "block" => true,
                "_block" => [
                    "end" => "}",
                    "nested" => "{"
                ]
            ],
            '(/find:")":"(":"condition">read:"condition"\/s|e\=>/nparenthesis>decrease\/varend\\' => [
                "class" => "ArrowMethodBlock",
                "parenthesis" => true,
                "block" => false,
            ],
            '/word\/s|e\=>/nparenthesis>decrease\/varend\\' => [
                "class" => "ArrowMethodBlock",
                "parenthesis" => false,
                "block" => false,
            ],
        ];
        $arrowMethodAsync = [];
        foreach ($arrowMethodInstruction as $key => $value) {
            $arrowMethodAsync[$key] = array_merge($value, ['async' => true]);
        }
        return [
            ...$arrowMethodInstruction,
            '/s|end\async/s|e\\' => [
                "_extend" => [
                    ...$arrowMethodAsync
                ]
            ]
        ];
    }

    public static function getKeyword(): array
    {
        return [
            '/s|end\/taken\/s|";"|e\\' => [
                "class" => "KeywordBlock"
            ]
        ];
    }

    public static function getClassGenerator(): array
    {
        return [
            '*[/find:"]":"[":"name">read:"name"\/s|e\(/find:")":"(":"arguments">read:"arguments"\/s|e\{' => [
                "class" => "ClassMethodBlock",
                "generator" => true,
                "constant_name" => true,
                "_block" => [
                    "end" => "}",
                    "nested" => "{"
                ],
            ]
        ];
    }

    public static function getMethodAndCaller(): array
    {
        return [
            '/s|end\/"#">isprivate|e\\' => [
                "_extend" => [
                    '[/find:"]":"[":"name">read:"name"\/s|e\(/find:")":"(":"arguments">read:"arguments"\/s|e\{' => [
                        "class" => "ClassMethodBlock",
                        "constant_name" => true,
                        "_block" => [
                            "end" => "}",
                            "nested" => "{"
                        ],
                    ],
                    '/word:"name"\/s|e\(' => [
                        "class" => "CallerBlock",
                        "_block" => [
                            "end" => ")",
                            "nested" => "("
                        ],
                        "_extend" => [
                            /* CLASS METHOD */
                            '/find:")":"(":"arguments">read:"arguments"\/s|e\{' => [
                                "class" => "ClassMethodBlock",
                                "_block" => [
                                    "end" => "}",
                                    "nested" => "{"
                                ],
                            ],
                        ],
                    ],
                ]
            ]
        ];
    }

    public static function getConsecutiveCaller(): array
    {
        return [
            '(/consecutivecaller>decrease\\' => [
                "class" => "CallerBlock",
                "consecutive" => true,
                "_block" => [
                    "end" => ")",
                    "nested" => "("
                ],
            ]
        ];
    }

    public static function getGetter(): array
    {
        return [
            '/s|end\get/s\/"#">isprivate|e\/word:"getter"\(/find:")":"(":"arguments">read:"arguments"\/s|e\{' => [
                "class" => "GetterClassMethodBlock",
                "_block" => [
                    "end" => "}",
                    "nested" => "{"
                ],
            ]
        ];
    }

    public static function getSetter(): array
    {
        return [
            '/s|end\set/s\/"#">isprivate|e\/word:"setter"\(/find:")":"(":"arguments">read:"arguments"\/s|e\{' => [
                "class" => "SetterClassMethodBlock",
                "_block" => [
                    "end" => "}",
                    "nested" => "{"
                ],
            ]
        ];
    }

    public static function getAsync(): array
    {
        return [
            '/s|end\async/s\/"#">isprivate|e\/word:"name"\(/find:")":"(":"arguments">read:"arguments"\/s|e\{' => [
                "class" => "AsyncClassMethodBlock",
                "_block" => [
                    "end" => "}",
                    "nested" => "{"
                ],
            ]
        ];
    }

    public static function getStaticGetter(): array
    {
        return [
            '/s|end\static/s\get/s\/"#">isprivate|e\/word:"getter"\(/find:")":"(":"arguments">read:"arguments"\/s|e\{' => [
                "class" => "StaticGetterClassMethodBlock",
                "_block" => [
                    "end" => "}",
                    "nested" => "{"
                ],
            ]
        ];
    }

    public static function getStaticSetter(): array
    {
        return [
            '/s|end\static/s\set/s\/"#">isprivate|e\/word:"setter"\(/find:")":"(":"arguments">read:"arguments"\/s|e\{' => [
                "class" => "StaticSetterClassMethodBlock",
                "_block" => [
                    "end" => "}",
                    "nested" => "{"
                ],
            ]
        ];
    }

    public static function getStaticAsync(): array
    {
        return [
            '/s|end\static/s\async/s\/"#">isprivate|e\/word:"name"\(/find:")":"(":"arguments">read:"arguments"\/s|e\{' => [
                "class" => "StaticAsyncClassMethodBlock",
                "_block" => [
                    "end" => "}",
                    "nested" => "{"
                ],
            ],
        ];
    }

    public static function getTry(): array
    {
        return [
            '/s|end\try/s|e\{' => [
                "class" => "TryBlock",
                "_block" => [
                    "end" => "}",
                    "nested" => "{"
                ]
            ],
        ];
    }

    public static function getCatch(): array
    {
        return [
            '/s|end\catch/s|e\(/find:")":"(":"exception">read:"exception"\/s|e\{' => [
                "class" => "CatchBlock",
                "_block" => [
                    "end" => "}",
                    "nested" => "{"
                ]
            ],
        ];
    }

    public static function getFinally(): array
    {
        return [
            '/s|end\finally/s|e\{' => [
                "class" => "FinallyBlock",
                "_block" => [
                    "end" => "}",
                    "nested" => "{"
                ]
            ],
        ];
    }

    public static function getFirstInChain(): array
    {
        return [
            '/s|end|e\/word:"first":false\/s|e\\' => [
                "_extend" => [
                    '/"?">optionalchain|e\/s|e\./s|e\/"#">isprivate|e\/word:"second"\\' => [
                        "class" => "ChainBlock",
                        "first" => true,
                        "_block" => [
                            "end" => '/chainend\\',
                            "include_end" => true,
                        ],
                        "_extend" => [
                            '/s|e\(/find:")":"(":"values_two">read:"values_two"\\' => [
                                "class" => "ChainBlock",
                                "first_method" => false,
                                "second_method" => true,
                                "first" => true,
                                "_block" => [
                                    "end" => '/chainend\\',
                                    "include_end" => true,
                                ],
                            ],
                            '/s|e\=/"!=">decrease\\' => [
                                "class" => "ChainBlock",
                                "first" => true,
                                "var" => true,
                                "_block" => [
                                    "end" => '/varend\\',
                                    "include_end" => true,
                                ],
                            ],
                            /* CHAIN (ARRAY ACCESS) */
                            ...self::ARRAY_CHAIN_INSTRUCTION,
                        ]
                    ],
                    '(/find:")":"(":"values">read:"values"\/s|e\/"?">optionalchain|e\/s|e\./s|e\/"#">isprivate|e\/word:"second"\\' => [
                        "class" => "ChainBlock",
                        "first" => true,
                        "first_method" => true,
                        "_block" => [
                            "end" => '/chainend\\',
                            "include_end" => true,
                        ],
                        "_extend" => [
                            '/s|e\(/find:")":"(":"values_two">read:"values_two"\\' => [
                                "class" => "ChainBlock",
                                "first_method" => true,
                                "second_method" => true,
                                "first" => true,
                                "_block" => [
                                    "end" => '/chainend\\',
                                    "include_end" => true,
                                ],
                            ],
                            '/s|e\=/"!=">decrease\\' => [
                                "class" => "ChainBlock",
                                "first" => true,
                                "var" => true,
                                "_block" => [
                                    "end" => '/varend\\',
                                    "include_end" => true,
                                ],
                            ]
                        ]
                    ],
                ]
            ],
        ];
    }

    public static function getNextInChain(): array
    {
        return [
            '/"?">optionalchain|s|e\./s|e\/"#">isprivate|e\/word\\' => [
                "class" => "SubChainBlock1",
                "_block" => [
                    "end" => '/chainend\\',
                    "include_end" => true,
                ],
                "_extend" => [
                    '/s|e\=/"!=">decrease\\' => [
                        "class" => "SubChainBlock2",
                        "var" => true,
                        "_block" => [
                            "end" => '/varend\\',
                            "include_end" => true,
                        ],
                    ],
                    '/s|e\(/find:")":"(":"values">read:"values"\\' => [
                        "class" => "SubChainBlock3",
                        "method" => true,
                    ],
                    /* CHAIN (ARRAY ACCESS) */
                    ...self::ARRAY_CHAIN_INSTRUCTION,
                ]
            ],
        ];
    }

    public static function getThis(): array
    {
        return [
            'this/".">decrease\\' => [
                "class" => "ThisBlock",
                "_block" => [
                    "end" => '/varend\\',
                    "include_end" => true,
                ],
            ],
        ];
    }

    public static function getEqual(): array
    {
        return [
            "==" => [
                "class" => "EqualBlock",
                "_extend" => [
                    "=" => [
                        "class" => "ExactBlock",
                    ]
                ]
            ],
        ];
    }

    public static function getUnequal(): array
    {
        return [
            "!=" => [
                "class" => "DifferentBlock",
                "_extend" => [
                    "=" => [
                        "class" => "DistinctBlock",
                    ]
                ]
            ],
        ];
    }

    public static function getDoWhile(): array
    {
        return [
            '/s|end\do/s|e\{' => [
                "class" => "DoWhileBlock",
                "_block" => [
                    "skip" => '/s|end\do/s|e\{',
                    "end" => '}/s|e\while/s|e\(/find:")":"(":"while">read:"while"\\',
                ]
            ],
        ];
    }

    public static function getWhileAndShortWhile(): array
    {
        return [
            '/s|end\while/s|e\(/find:")":"(":"condition">read:"condition"\/s|e\{' => [
                "class" => "WhileBlock",
                "_block" => [
                    "skip" => '{',
                    "end" => '}',
                ]
            ],
            '/s|end\while/s|e\(/find:")":"(":"condition">read:"condition"\/nparenthesis>decrease\/varend\\' => [
                "class" => "ShortWhileBlock"
            ],
        ];
    }

    public static function getElseAndElseIf(): array
    {
        return [
            '/s|e\else' => [
                "_extend" => [
                    "/s|e\{" => [
                        "class" => "ElseBlock",
                        "_block" => [
                            "skip" => '{',
                            "end" => '}',
                        ]
                    ],
                    '/s\if/s|e\(/find:")":"(":"values">read:"values"\/s|e\{' => [
                        "class" => "ElseIfBlock",
                        "_block" => [
                            "skip" => '{',
                            "end" => '}',
                        ]
                    ]
                ]
            ],
        ];
    }

    public static function getFalse(): array
    {
        return [
            '/s|end\false' => [
                "class" => "FalseBlock"
            ],
        ];
    }

    public static function getTrue(): array
    {
        return [
            '/s|end\true' => [
                "class" => "TrueBlock"
            ],
        ];
    }

    public static function getForAndShortFor(): array
    {
        return [
            '/s|end\for/s|e\(/find:")":"(":"condition">read:"condition"\/s|e\{' => [
                "class" => "ForBlock",
                "_block" => [
                    "end" => "}",
                    "nested" => "{"
                ]
            ],
            '/s|end\for/s|e\(/find:")":"(":"condition">read:"condition"\/nparenthesis>decrease\/varend\\' => [
                "class" => "ShortForBlock"
            ],
        ];
    }

    public static function getForOfAndInCondition(): array
    {
        $forOfConditionBlock = [
            "class" => "OfConditionBlock"
        ];
        $forInConditionBlock = [
            "class" => "InConditionBlock"
        ];
        return [
            '/s|end|"("\\' => [
                "_extend" => [
                    'const/s\/word:"var"\/s\\' => [
                        "_extend" => [
                            'of/s\/word:"iterable"\\' => [
                                "type" => "const", ...$forOfConditionBlock
                            ],
                            'in/s\/word:"iterable"\\' => [
                                "type" => "const", ...$forOfConditionBlock
                            ],
                        ]
                    ],

                    'let/s\/word:"var"\/s\\' => [
                        "_extend" => [
                            'of/s\/word:"iterable"\\' => ["type" => "let", ...$forOfConditionBlock],
                            'in/s\/word:"iterable"\\' => ["type" => "let", ...$forInConditionBlock],
                        ]
                    ],

                    'var/s\/word:"var"\/s\\' => [
                        "_extend" => [
                            'of/s\/word:"iterable"\\' => ["type" => "var", ...$forOfConditionBlock],
                            'in/s\/word:"iterable"\\' => ["type" => "var", ...$forInConditionBlock],
                        ]
                    ],

                    '/word:"var"\/s\\' => [
                        "_extend" => [
                            'of/s\/word:"iterable"\\' => ["type" => "empty", ...$forOfConditionBlock],
                            'in/s\/word:"iterable"\\' => ["type" => "empty", ...$forInConditionBlock],
                        ]
                    ],
                ]
            ],
        ];
    }

    public static function getFunctionAndGenerator(): array
    {
        return [
            '/s|end\function' => [
                "_extend" => [
                    // GENERATOR
                    '*/s\/word:"name"\(/find:")":"(":"arguments">read:"arguments"\/s|e\{' => [
                        "class" => "FunctionBlock",
                        "generator" => true,
                        "_block" => [
                            "end" => "}",
                            "nested" => "{"
                        ]
                    ],
                    '/s\/word:"name"\(/find:")":"(":"arguments">read:"arguments"\/s|e\{' => [
                        "class" => "FunctionBlock",
                        "_block" => [
                            "end" => "}",
                            "nested" => "{"
                        ]
                    ],
                    // ANONYMOUS
                    '/s|e\(/find:")":"(":"arguments">read:"arguments"\/s|e\{' => [
                        "class" => "FunctionBlock",
                        "anonymous" => true,
                        "_block" => [
                            "end" => "}",
                            "nested" => "{"
                        ]
                    ],
                    '*/s|e\(/find:")":"(":"arguments">read:"arguments"\/s|e\{' => [
                        "class" => "FunctionBlock",
                        "generator" => true,
                        "anonymous" => true,
                        "_block" => [
                            "end" => "}",
                            "nested" => "{"
                        ]
                    ],
                ]
            ],
        ];
    }

    public static function getNewInstance(): array
    {
        return [
            '/s|end\new/s\/word:"class"\\' => [
                "class" => "NewInstanceBlock",
                "_extend" => [
                    '/s|e\(/find:")":"(":"values">read:"values"\\' => [
                        "class" => "NewClassInstanceBlock",
                        "parenthesis" => true,
                        "_block" => [
                            "end" => '/varend:false\\',
                            "include_end" => true,
                        ],
                    ]
                ]
            ],
        ];
    }

    public static function getObjectAndSpreadObject(): array
    {
        $objectItemBlock = [
            "class" => "ObjectValueBlock",
            "_block" => [
                "end" => '/objectend\\',
                "include_end" => true,
            ],
        ];
        return [
            '{' => [
                "class" => "ObjectBlock",
                "_block" => [
                    "end" => "}",
                    "nested" => "{"
                ],
                "_extend" => [
                    '/find:"}":"{":"deconstruction">read:"deconstruction"\/s|e\=' => [
                        "class" => "ObjectDeconstructionAssignmentBlock",
                        "_block" => [
                            "end" => "/varend\\",
                            "include_end" => true,
                        ]
                    ]
                ]
            ],
            '/s|e|"{"\\' => [
                "_extend" => [
                    "'/strend:`'`\/s|e\:" => $objectItemBlock,
                    '`/strend:"`":"template">templateliteral\/s|e\:' => $objectItemBlock,
                    '"/strend:`"`\/s|e\:' => $objectItemBlock,
                    '/word:"name"\/s|e\:' => $objectItemBlock,
                    '[/find:"]":"[":"key">read:"key"\/s|e\:' => ["key" => true, ...$objectItemBlock],
                ]
            ],
            '.../s|e\{' => [
                "spread" => true,
                "class" => "ObjectBlock",
                "_block" => [
                    "end" => "}",
                    "skip" => "{",
                ],
            ],
        ];
    }

    public static function getReturn(): array
    {
        return [
            '/s|end\return/s|symbol>decrease\\' => [
                "_block" => [
                    "end" => '/varend\\',
                    "include_end" => true,
                ],
                "class" => "ReturnBlock",
            ],
        ];
    }

    public static function getSwitchAndCases(): array
    {
        return [
            '/s|end\switch/s|e\(/find:")":"(":"values">read:"values"\/s|e\{' => [
                "class" => "SwitchBlock",
                "_block" => [
                    "end" => "}",
                    "nested" => "{"
                ],
            ],
            '/s|end\case/s\/word:"case"\/s|e\:' => [
                "class" => "SwitchCaseBlock",
                "_block" => [
                    "end" => ['/case\\', 'break'],
                    "nested" => '/s|end\case/s\/word:"case"\/s|e\:',
                ]
            ],
            '/s|end\default/s|e\:' => [
                "class" => "SwitchDefaultCaseBlock",
                "_block" => [
                    "end" => ['/case\\', 'break'],
                    "nested" => '/s|end\default/s|e\:',
                ]
            ],
        ];
    }

    public static function getSymbol(): array
    {
        return [
            '/symbol:"first"\\' => [
                'class' => "SymbolBlock",
                "type" => "single",
                "_extend" => [
                    '/symbol:"second"\\' => [
                        'class' => "SymbolBlock",
                        "type" => "double",
                        "_extend" => [
                            '/symbol:"third"\\' => [
                                'class' => "SymbolBlock",
                                "type" => "triple",
                            ]
                        ]
                    ]
                ]
            ],
        ];
    }

    public static function getYeld(): array
    {
        return [
            '/s|end\yield/"*">isgenerator|e\/s|symbol>decrease\\' => [
                "_block" => [
                    "end" => '/varend\\',
                    "include_end" => true,
                ],
                "class" => "YieldBlock",
            ],
        ];
    }

    public static function getScope(): array
    {
        return [
            '(' => [
                "class" => "ScopeBlock",
                "_block" => [
                    "end" => ")",
                    "nested" => "(",
                ]
            ],
        ];
    }

    public static function getImport(): array
    {
        $importAliasStringItemBlock = [
            "class" => "ImportAliasBlock",
            "string" => true,
        ];

        $fromItemBlock = [
            "class" => "FromBlock"
        ];

        return [
            '/s|end\import/s|e\\' => [
                "_extend" => [
                    '/s|e\(' => [
                        "class" => "CallerBlock",
                        "import" => true,
                        "_block" => [
                            "end" => ")",
                            "nested" => "(",
                        ]
                    ],
                    '/s\\' => [
                        "_block" => [
                            "end" => '/varend:false\\',
                            "include_end" => true,
                        ],
                        "class" => "ImportBlock",
                    ]
                ]
            ],
            '/s|end\/word:"name"\/s\as/s\/word:"alias"\\' => [
                "class" => "AliasBlock",
            ],
            '\'/strend:`\'`\/s|e\as/s\/word:"alias"\\' => $importAliasStringItemBlock,
            '`/strend:"`":"template">templateliteral\/s|e\as/s\/word:"alias"\\' => $importAliasStringItemBlock,
            '"/strend:`"`\/s|e\as/s\/word:"alias"\\' => $importAliasStringItemBlock,
            '*/s|e\as/s\/word:"alias"\\' => [
                "class" => "ImportAllAliasBlock",
            ],
            '/s|end\default/s\as/s\/word:"alias"\\' => [
                "class" => "ImportAliasBlock",
                "default" => true,
            ],
            '/s|end\from/s\\' => [
                "_extend" => [
                    "'/strend:`'`\\" => $fromItemBlock,
                    '`/strend:"`":"template">templateliteral\\' => $fromItemBlock,
                    '"/strend:`"`\\' => $fromItemBlock,
                ]
            ],
        ];
    }

    public static function getExport(): array
    {
        $exportFromBlock = [
            "class" => "ExportFromBlock",
            "object" => true,
        ];

        $exportAliasStringBlock = [
            "class" => "ExportAliasBlock",
            "string" => true,
        ];

        return [
            '/s|end\/word:"name"\/s\as/s|e\\' => [
                "_extend" => [
                    '`/strend:"`":"template">templateliteral\\' => $exportAliasStringBlock,
                    '\'/strend:// Log ::\'// Log ::\\' => $exportAliasStringBlock,
                    '"/strend:`"`\\' => $exportAliasStringBlock,
                ]
            ],
            '/s|end\/word:"alias"\/s\as/s\default' => [
                "class" => "ExportAliasBlock",
                "default" => true,
            ],
            '/s|end\export' => [
                "_extend" => [
                    '/s\\' => [
                        "class" => "ExportBlock",
                        "_block" => [
                            "end" => '/varend:false\\',
                            "include_end" => true,
                        ],
                        "_extend" => [
                            'default/s\\' => [
                                "_block" => [
                                    "end" => '/varend:false\\',
                                    "include_end" => true,
                                ],
                                "class" => "ExportBlock",
                                "default" => true,
                            ],
                            '{' => [
                                "class" => "ExportBlock",
                                "object" => true,
                                "_block" => [
                                    "end" => "}",
                                    "nested" => "{",
                                ],
                                "_extend" => [
                                    '/find:"}":"{":"object">read:"object"\/s|e\from/s|e\\' => [
                                        "_extend" => [
                                            '`/strend:"`":"template">templateliteral\\' => $exportFromBlock,
                                            '\'/strend:`\'`\\' => $exportFromBlock,
                                            '"/strend:`"`\\' => $exportFromBlock,
                                        ]
                                    ]
                                ]
                            ],
                            '*' => [
                                "_extend" => [
                                    '/s\as/s\/word:"alias"\\' => [
                                        "class" => "ExportBlock",
                                        "all" => true,
                                        "alias" => true,
                                        "_block" => [
                                            "end" => '/varend:false\\',
                                            "include_end" => true,
                                        ],
                                    ],
                                ],
                                "class" => "ExportBlock",
                                "all" => true,
                                "_block" => [
                                    "end" => '/varend:false\\',
                                    "include_end" => true,
                                ],
                            ],
                        ]
                    ],
                    '{' => [
                        "class" => "ExportBlock",
                        "object" => true,
                        "_block" => [
                            "end" => "}",
                            "nested" => "{",
                        ]
                    ],
                    '*' => [
                        "_extend" => [
                            '/s\as/s\/word:"alias"\\' => [
                                "class" => "ExportBlock",
                                "all" => true,
                                "alias" => true
                            ],
                        ],
                        "class" => "ExportBlock",
                        "all" => true,
                    ],
                ],
            ],
        ];
    }

    public static function getNumber(): array
    {
        return [
            "/number\\" => [
                "class" => "NumberBlock"
            ]
        ];
    }
}
