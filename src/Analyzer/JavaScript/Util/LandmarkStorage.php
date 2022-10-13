<?php declare(strict_types=1);

namespace Tetraquark\Analyzer\JavaScript\Util;

/**
 * @codeCoverageIgnore
 */
abstract class LandmarkStorage
{
    public const _BLOCK_VAREND = [
        "end" => '/varend\\',
        "include_end" => true,
    ];

    public const _BLOCK_VAREND_NO_COMMA = [
        "end" => '/varend:false\\',
        "include_end" => true,
    ];

    public const _BLOCK_OBJECT = [
        "end" => "}",
        "nested" => "{",
    ];

    public const _BLOCK_PARENTHESIS = [
        "end" => ")",
        "nested" => "(",
    ];

    public const _BLOCK_SHORT = [
        "end" => "/getnext\\",
    ];

    public const APOSTROPHE_SEGMENT = '\'/strend:"\'"\\';
    public const TEMPLATE_LITERAL_SEGMENT = '`/strend:"`":"template">templateliteral:"template">read:"template"\\';
    public const QUOTE_SEGMENT = '"/strend:\'"\'\\';
    public const WORD_SEPERATOR_SEGMENT = '/s|end\\';
    public const PRIVATE_SEGMENT = '/"#">isprivate|e\\';

    public const ARRAY_CHAIN_INSTRUCTION = [
        '/s|e\[/find:"]":"[":"index">read:"index"\\' => [
            "class" => "ChainBlock",
            "array" => true,
            "_block" => self::_BLOCK_VAREND,
            "_extend" => [
                '/s|e\=/"!=">decrease\\' => [
                    "class" => "ChainBlock",
                    "array" => true,
                    "var" => true,
                    "_block" => self::_BLOCK_VAREND,
                ],
                '/s|e\(/find:")":"(":"values">read:"values"\\' => [
                    "class" => "ChainBlock",
                    "array" => true,
                    "method" => true,
                ],
            ],
        ]
    ];

    public static function getIfAndShortIf(): array
    {
        return [
            self::WORD_SEPERATOR_SEGMENT . 'if/s|e\(' . self::genFindParenthesis() =>  [
                "_extend" => [
                    '/s|e\{' => [
                        "class" => "IfBlock",
                        "_block" => self::_BLOCK_OBJECT,
                    ],
                    /* SHORT IF */
                    '/nparenthesis>decrease\\' => [
                        "class" => "ShortIfBlock",
                        "_block" => self::_BLOCK_SHORT,
                    ],
                ]
            ]
        ];
    }

    public static function getClassDefinition(): array
    {
        $class = "ClassBlock";
        $classExtendBlock = [
            '/s|e\{' => [
                "class" => $class,
                "_block" => self::_BLOCK_OBJECT,
            ],
            '/s\extends/word:"extends_name"\/s|e\{' => [
                "class" => $class,
                "extends" => true,
                "_block" => self::_BLOCK_OBJECT,
            ]
        ];
        $anonymousClasses = [];
        foreach ($classExtendBlock as $key => $value) {
            $anonymousClasses[$key] = ["anonymouse" => true, ...$value];
        }
        return [
            self::WORD_SEPERATOR_SEGMENT . 'class' => [
                '_extend' => [
                    ...$anonymousClasses,
                    '/s\/word:"class_name"\\' => [
                        "_extend" => $classExtendBlock
                    ]
                ]
            ],
        ];
    }

    public static function getVariableDefinitions(): array
    {
        return [
            /* LET */
            self::WORD_SEPERATOR_SEGMENT . 'let/s\\' => [
                "class" => "LetVariableBlock",
                "_block" => self::_BLOCK_VAREND_NO_COMMA,
            ],
            /* CONST */
            self::WORD_SEPERATOR_SEGMENT . 'const/s\\' => [
                "class" => "ConstVariableBlock",
                "_block" => self::_BLOCK_VAREND_NO_COMMA,
            ],
            /* VAR */
            self::WORD_SEPERATOR_SEGMENT . 'var/s\\' => [
                "class" => "VarVariableBlock",
                "_block" => self::_BLOCK_VAREND_NO_COMMA,
            ],
        ];
    }

    public static function getVariable(): array
    {
        $class = "VariableInstanceBlock";
        return [
            self::PRIVATE_SEGMENT . '/word:"name"\\' => [
                "class" => $class,
                "empty" => true,
                "_extend" => [
                    '/s|e\\' => [
                        "_extend" => [
                            '=/"!=">decrease\\' => [
                                "class" => $class,
                                "replace" => true,
                                "_block" => self::_BLOCK_VAREND,
                            ],
                            '/assignment\\=/"!=">decrease\\' => [
                                "class" => $class,
                                "_block" => self::_BLOCK_VAREND,
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
            self::WORD_SEPERATOR_SEGMENT . 'static' => [
                "_extend" => [
                    /* STATIC INITIALIZATION */
                    '/s|e\\' => [
                        "_extend" => [
                            '{' => [
                                "class" => "StaticInitializationBlock",
                                "_block" => self::_BLOCK_OBJECT,
                            ],
                            self::PRIVATE_SEGMENT . '[/find:"]":"[":"name">read:"name"\/s|e\(' . self::genFindParenthesis('arguments') . '/s|e\{' => [
                                "class" => "StaticClassMethodBlock",
                                "constant_name" => true,
                                "_block" => self::_BLOCK_OBJECT,
                            ],
                        ]
                    ],
                    '/s\\' . self::PRIVATE_SEGMENT . '/word:"name"\/s|e\\' => [
                        "class" => "StaticVariableInstanceBlock",
                        "empty" => true,
                        "_extend" => [
                            '=/"!=">decrease\\' => [
                                "class" => "StaticVariableInstanceBlock",
                                "replace" => true,
                                "_block" => self::_BLOCK_VAREND,
                            ],
                            '(' . self::genFindParenthesis('arguments') . '/s|e\{' => [
                                "class" => "StaticClassMethodBlock",
                                "_block" => self::_BLOCK_OBJECT,
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
                        "_block" => self::_BLOCK_VAREND,
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
            self::APOSTROPHE_SEGMENT => [
                "class" => "StringBlock",
            ]
        ];
    }


    public static function getTemplateLiteral(): array
    {
        return [
            self::TEMPLATE_LITERAL_SEGMENT => [
                "class" => "StringBlock",
            ]
        ];
    }


    public static function getQuote(): array
    {
        return [
            self::QUOTE_SEGMENT => [
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
            '/s|e\(' . self::genFindParenthesis() . '/s|e\=>/s|e\{' => [
                "class" => "ArrowMethodBlock",
                "parenthesis" => true,
                "block" => true,
                "_block" => self::_BLOCK_OBJECT,
            ],
            '/s|e\(' . self::genFindParenthesis('arguments') . '/s|e\=>/nparenthesis>decrease\\' => [
                "class" => "ArrowMethodBlock",
                "parenthesis" => true,
                "block" => false,
                "_block" => self::_BLOCK_VAREND,
            ],
        ];
        $arrowMethodAsync = [];
        foreach ($arrowMethodInstruction as $key => $value) {
            $arrowMethodAsync[$key] = array_merge($value, ['async' => true]);
        }
        return [
            self::WORD_SEPERATOR_SEGMENT. '/word\/s|e\=>/s|e\{' => [
                "class" => "ArrowMethodBlock",
                "parenthesis" => false,
                "block" => true,
                "_block" => self::_BLOCK_OBJECT,
            ],
            self::WORD_SEPERATOR_SEGMENT . '/word\/s|e\=>/nparenthesis>decrease\\' => [
                "class" => "ArrowMethodBlock",
                "parenthesis" => false,
                "block" => false,
                "_block" => self::_BLOCK_VAREND,
            ],
            ...$arrowMethodInstruction,
            self::WORD_SEPERATOR_SEGMENT . 'async' => [
                "_extend" => [
                    '/s\/word\/s|e\=>/s|e\{' => [
                        "class" => "ArrowMethodBlock",
                        "parenthesis" => false,
                        "block" => true,
                        'async' => true,
                        "_block" => self::_BLOCK_OBJECT,
                    ],
                    '/s\/word\/s|e\=>/nparenthesis>decrease\\' => [
                        "class" => "ArrowMethodBlock",
                        "parenthesis" => false,
                        'async' => true,
                        "block" => false,
                        "_block" => self::_BLOCK_VAREND,
                    ],
                    ...$arrowMethodAsync,
                ]
            ]
        ];
    }

    public static function getKeyword(): array
    {
        return [
            self::WORD_SEPERATOR_SEGMENT . '/taken\/s|";"|e\\' => [
                "class" => "KeywordBlock"
            ]
        ];
    }

    public static function getConstantMethodAndClassMethodAndCaller(): array
    {
        return [
            // '/"#">isprivate|e\\'
            self::WORD_SEPERATOR_SEGMENT . '/"*">isgenerator|e\/s|e\\' . self::PRIVATE_SEGMENT => [
                "_extend" => [
                    '/s|e\[/find:"]":"[":"name">read:"name"\/s|e\(' . self::genFindParenthesis('arguments') . '/s|e\{' => [
                        "class" => "ClassMethodBlock",
                        "constant_name" => true,
                        "_block" => self::_BLOCK_OBJECT,
                    ],
                    '/word:"name"\/s|e\(' => [
                        "class" => "CallerBlock",
                        "_block" => self::_BLOCK_PARENTHESIS,
                        "_extend" => [
                            /* CLASS METHOD */
                            self::genFindParenthesis('arguments') . '/s|e\{' => [
                                "class" => "ClassMethodBlock",
                                "_block" => self::_BLOCK_OBJECT,
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
                "_block" => self::_BLOCK_PARENTHESIS,
            ]
        ];
    }

    public static function getGetter(): array
    {
        return [
            self::WORD_SEPERATOR_SEGMENT . 'get' . self::getEttersFinish('getter') => [
                "class" => "GetterClassMethodBlock",
                "_block" => self::_BLOCK_OBJECT,
            ]
        ];
    }

    public static function getSetter(): array
    {
        return [
            self::WORD_SEPERATOR_SEGMENT . 'set' . self::getEttersFinish('setter') => [
                "class" => "SetterClassMethodBlock",
                "_block" => self::_BLOCK_OBJECT,
            ]
        ];
    }

    public static function getAsync(): array
    {
        return [
            self::WORD_SEPERATOR_SEGMENT . 'async' . self::getEttersFinish('name') => [
                "class" => "AsyncClassMethodBlock",
                "_block" => self::_BLOCK_OBJECT,
            ]
        ];
    }

    public static function getStaticGetter(): array
    {
        return [
            self::WORD_SEPERATOR_SEGMENT . 'static/s\get' . self::getEttersFinish('getter') => [
                "class" => "StaticGetterClassMethodBlock",
                "_block" => self::_BLOCK_OBJECT,
            ]
        ];
    }

    public static function getStaticSetter(): array
    {
        return [
            self::WORD_SEPERATOR_SEGMENT . 'static/s\set' . self::getEttersFinish('setter') => [
                "class" => "StaticSetterClassMethodBlock",
                "_block" => self::_BLOCK_OBJECT,
            ]
        ];
    }

    public static function getStaticAsync(): array
    {
        return [
            self::WORD_SEPERATOR_SEGMENT . 'static/s\async' . self::getEttersFinish('name') => [
                "class" => "StaticAsyncClassMethodBlock",
                "_block" => self::_BLOCK_OBJECT,
            ],
        ];
    }

    public static function getTry(): array
    {
        return [
            self::WORD_SEPERATOR_SEGMENT . 'try/s|e\{' => [
                "class" => "TryBlock",
                "_block" => self::_BLOCK_OBJECT,
            ],
        ];
    }

    public static function getCatch(): array
    {
        return [
            self::WORD_SEPERATOR_SEGMENT . 'catch/s|e\(' . self::genFindParenthesis('exception') . '/s|e\{' => [
                "class" => "CatchBlock",
                "_block" => self::_BLOCK_OBJECT,
            ],
        ];
    }

    public static function getFinally(): array
    {
        return [
            self::WORD_SEPERATOR_SEGMENT . 'finally/s|e\{' => [
                "class" => "FinallyBlock",
                "_block" => self::_BLOCK_OBJECT,
            ],
        ];
    }

    public static function getFirstInChain(): array
    {
        return [
            '/s|end|e\/word:"first":false\/s|e\\' => [
                "_extend" => [
                    '/"?">optionalchain|e\/s|e\./s|e\\' . self::PRIVATE_SEGMENT . '/word:"second"\\' => [
                        "class" => "ChainBlock",
                        "first" => true,
                        "_block" => [
                            "end" => '/chainend\\',
                            "include_end" => true,
                        ],
                        "_extend" => [
                            '/s|e\(' . self::genFindParenthesis('values_two') => [
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
                                "_block" => self::_BLOCK_VAREND,
                            ],
                            /* CHAIN (ARRAY ACCESS) */
                            ...self::ARRAY_CHAIN_INSTRUCTION,
                        ]
                    ],
                    '(' . self::genFindParenthesis('values') . '/s|e\/"?">optionalchain|e\/s|e\./s|e\\' . self::PRIVATE_SEGMENT . '/word:"second"\\' => [
                        "class" => "ChainBlock",
                        "first" => true,
                        "first_method" => true,
                        "_block" => [
                            "end" => '/chainend\\',
                            "include_end" => true,
                        ],
                        "_extend" => [
                            '/s|e\(' . self::genFindParenthesis('values_two') => [
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
                                "_block" => self::_BLOCK_VAREND,
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
            '/"?">optionalchain|s|e\./s|e\\' . self::PRIVATE_SEGMENT . '/word\\' => [
                "class" => "SubChainBlock1",
                "_block" => [
                    "end" => '/chainend\\',
                    "include_end" => true,
                ],
                "_extend" => [
                    '/s|e\=/"!=">decrease\\' => [
                        "class" => "SubChainBlock2",
                        "var" => true,
                        "_block" => self::_BLOCK_VAREND,
                    ],
                    '/s|e\(' . self::genFindParenthesis('values') => [
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
            'this/s|e\.' => [
                "class" => "ThisBlock",
                "_block" => self::_BLOCK_VAREND,
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
            self::WORD_SEPERATOR_SEGMENT . 'do/s|e\{' => [
                "class" => "DoWhileBlock",
                "_block" => [
                    "skip" => self::WORD_SEPERATOR_SEGMENT . 'do/s|e\{',
                    "end" => '}/s|e\while/s|e\(' . self::genFindParenthesis('while'),
                ]
            ],
        ];
    }

    public static function getWhileAndShortWhile(): array
    {
        return [
            self::WORD_SEPERATOR_SEGMENT . 'while/s|e\(' . self::genFindParenthesis('condition') . '/s|e\{' => [
                "class" => "WhileBlock",
                "_block" => self::_BLOCK_OBJECT,
            ],
            self::WORD_SEPERATOR_SEGMENT . 'while/s|e\(' . self::genFindParenthesis('condition') . '/nparenthesis>decrease\\' => [
                "class" => "ShortWhileBlock",
                "_block" => self::_BLOCK_SHORT,
            ],
        ];
    }

    public static function getElseAndElseIf(): array
    {
        return [
            self::WORD_SEPERATOR_SEGMENT . 'else' => [
                "_extend" => [
                    "/s|e\{" => [
                        "class" => "ElseBlock",
                        "_block" => self::_BLOCK_OBJECT,
                    ],
                    '/s\if/s|e\(' . self::genFindParenthesis('values') . '/s|e\{' => [
                        "class" => "ElseIfBlock",
                        "_block" => self::_BLOCK_OBJECT,
                    ]
                ]
            ],
        ];
    }

    public static function getFalse(): array
    {
        return [
            self::WORD_SEPERATOR_SEGMENT . 'false' => [
                "class" => "FalseBlock"
            ],
        ];
    }

    public static function getTrue(): array
    {
        return [
            self::WORD_SEPERATOR_SEGMENT . 'true' => [
                "class" => "TrueBlock"
            ],
        ];
    }

    public static function getForAndShortFor(): array
    {
        return [
            self::WORD_SEPERATOR_SEGMENT . 'for/s|e\(' . self::genFindParenthesis('condition') . '/s|e\{' => [
                "class" => "ForBlock",
                "_block" => self::_BLOCK_OBJECT,
            ],
            self::WORD_SEPERATOR_SEGMENT . 'for/s|e\(' . self::genFindParenthesis('condition') . '/nparenthesis>decrease\\' => [
                "class" => "ShortForBlock",
                "_block" => self::_BLOCK_SHORT,
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
        $parenthesis = '(' . self::genFindParenthesis('arguments') . '/s|e\{';
        $class = "FunctionBlock";
        return [
            self::WORD_SEPERATOR_SEGMENT . 'function' => [
                "_extend" => [
                    // GENERATOR
                    '*/s\/word:"name"\\' . $parenthesis => [
                        "class" => $class,
                        "generator" => true,
                        "_block" => self::_BLOCK_OBJECT,
                    ],
                    '/s\/word:"name"\\' . $parenthesis => [
                        "class" => $class,
                        "_block" => self::_BLOCK_OBJECT,
                    ],
                    // ANONYMOUS
                    '/s|e\\' . $parenthesis => [
                        "class" => $class,
                        "anonymous" => true,
                        "_block" => self::_BLOCK_OBJECT,
                    ],
                    '*/s|e\\' . $parenthesis => [
                        "class" => $class,
                        "generator" => true,
                        "anonymous" => true,
                        "_block" => self::_BLOCK_OBJECT,
                    ],
                ]
            ],
        ];
    }

    public static function getNewInstance(): array
    {
        return [
            self::WORD_SEPERATOR_SEGMENT . 'new/s\/word:"class"\\' => [
                "class" => "NewInstanceBlock",
                "_extend" => [
                    '/s|e\(' . self::genFindParenthesis('values') => [
                        "class" => "NewClassInstanceBlock",
                        "parenthesis" => true,
                        "_block" => self::_BLOCK_VAREND_NO_COMMA,
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
                "_block" => self::_BLOCK_OBJECT,
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
                    self::APOSTROPHE_SEGMENT . '/s|e\:' => $objectItemBlock,
                    self::TEMPLATE_LITERAL_SEGMENT . '/s|e\:' => $objectItemBlock,
                    self::QUOTE_SEGMENT . '/s|e\:' => $objectItemBlock,
                    '/word:"name"\/s|e\:' => $objectItemBlock,
                    '[/find:"]":"[":"key">read:"key"\/s|e\:' => ["key" => true, ...$objectItemBlock],
                ]
            ],
            '.../s|e\{' => [
                "spread" => true,
                "class" => "ObjectBlock",
                "_block" => self::_BLOCK_OBJECT,
            ],
        ];
    }

    public static function getReturn(): array
    {
        return [
            self::WORD_SEPERATOR_SEGMENT . 'return/s|symbol>decrease\\' => [
                "_block" => self::_BLOCK_VAREND,
                "class" => "ReturnBlock",
            ],
        ];
    }

    public static function getSwitchAndCases(): array
    {
        return [
            self::WORD_SEPERATOR_SEGMENT . 'switch/s|e\(' . self::genFindParenthesis('values') . '/s|e\{' => [
                "class" => "SwitchBlock",
                "_block" => self::_BLOCK_OBJECT,
            ],
            self::WORD_SEPERATOR_SEGMENT . 'case/s\/word:"case"\/s|e\:' => [
                "class" => "SwitchCaseBlock",
                "_block" => [
                    "end" => ['/case\\', 'break'],
                    "nested" => self::WORD_SEPERATOR_SEGMENT . 'case/s\/word:"case"\/s|e\:',
                ]
            ],
            self::WORD_SEPERATOR_SEGMENT . 'default/s|e\:' => [
                "class" => "SwitchDefaultCaseBlock",
                "_block" => [
                    "end" => ['/case\\', 'break'],
                    "nested" => self::WORD_SEPERATOR_SEGMENT . 'default/s|e\:',
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
            self::WORD_SEPERATOR_SEGMENT . 'yield/"*">isgenerator|e\\' => [
                "_block" => self::_BLOCK_VAREND,
                "class" => "YieldBlock",
            ],
        ];
    }

    public static function getScope(): array
    {
        return [
            '(' => [
                "class" => "ScopeBlock",
                "_block" => self::_BLOCK_PARENTHESIS,
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
            self::WORD_SEPERATOR_SEGMENT . 'import/s|e\\' => [
                "_extend" => [
                    '/s|e\(' => [
                        "class" => "CallerBlock",
                        "import" => true,
                        "_block" => self::_BLOCK_PARENTHESIS,
                    ],
                    '/s\\' => [
                        "_block" => self::_BLOCK_VAREND_NO_COMMA,
                        "class" => "ImportBlock",
                    ]
                ]
            ],
            self::WORD_SEPERATOR_SEGMENT . '/word:"name"\/s\as/s\/word:"alias"\\' => [
                "class" => "AliasBlock",
            ],
            self::APOSTROPHE_SEGMENT . '/s|e\as/s\/word:"alias"\\' => $importAliasStringItemBlock,
            self::TEMPLATE_LITERAL_SEGMENT . '/s|e\as/s\/word:"alias"\\' => $importAliasStringItemBlock,
            self::QUOTE_SEGMENT . '/s|e\as/s\/word:"alias"\\' => $importAliasStringItemBlock,
            '*/s|e\as/s\/word:"alias"\\' => [
                "class" => "ImportAllAliasBlock",
            ],
            self::WORD_SEPERATOR_SEGMENT . 'default/s\as/s\/word:"alias"\\' => [
                "class" => "ImportAliasBlock",
                "default" => true,
            ],
            self::WORD_SEPERATOR_SEGMENT . 'from/s\\' => [
                "_extend" => [
                    self::APOSTROPHE_SEGMENT => $fromItemBlock,
                    self::TEMPLATE_LITERAL_SEGMENT => $fromItemBlock,
                    self::QUOTE_SEGMENT => $fromItemBlock,
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

        $class = "ExportBlock";

        return [
            self::WORD_SEPERATOR_SEGMENT . '/word:"name"\/s\as/s|e\\' => [
                "_extend" => [
                    self::TEMPLATE_LITERAL_SEGMENT => $exportAliasStringBlock,
                    self::APOSTROPHE_SEGMENT => $exportAliasStringBlock,
                    self::QUOTE_SEGMENT => $exportAliasStringBlock,
                ]
            ],
            self::WORD_SEPERATOR_SEGMENT . '/word:"alias"\/s\as/s\default' => [
                "class" => "ExportAliasBlock",
                "default" => true,
            ],
            self::WORD_SEPERATOR_SEGMENT . 'export' => [
                "_extend" => [
                    '/s\\' => [
                        "class" => $class,
                        "_block" => self::_BLOCK_VAREND_NO_COMMA,
                        "_extend" => [
                            'default/s\\' => [
                                "_block" => self::_BLOCK_VAREND_NO_COMMA,
                                "class" => $class,
                                "default" => true,
                            ],
                            '{' => [
                                "class" => $class,
                                "object" => true,
                                "_block" => self::_BLOCK_OBJECT,
                                "_extend" => [
                                    '/find:"}":"{":"object">read:"object"\/s|e\from/s|e\\' => [
                                        "_extend" => [
                                            self::TEMPLATE_LITERAL_SEGMENT => $exportFromBlock,
                                            self::APOSTROPHE_SEGMENT => $exportFromBlock,
                                            self::QUOTE_SEGMENT => $exportFromBlock,
                                        ]
                                    ]
                                ]
                            ],
                            '*' => [
                                "_extend" => [
                                    '/s\as/s\/word:"alias"\\' => [
                                        "class" => $class,
                                        "all" => true,
                                        "alias" => true,
                                        "_block" => self::_BLOCK_VAREND_NO_COMMA,
                                    ],
                                ],
                                "class" => $class,
                                "all" => true,
                                "_block" => self::_BLOCK_VAREND_NO_COMMA,
                            ],
                        ]
                    ],
                    '{' => [
                        "class" => $class,
                        "object" => true,
                        "_block" => self::_BLOCK_OBJECT,
                    ],
                    '*' => [
                        "_extend" => [
                            '/s\as/s\/word:"alias"\\' => [
                                "class" => $class,
                                "all" => true,
                                "alias" => true
                            ],
                        ],
                        "class" => $class,
                        "all" => true,
                    ],
                ],
            ],
        ];
    }

    public static function getNumber(): array
    {
        return [
            '/number\\' => [
                "class" => "NumberBlock"
            ]
        ];
    }

    protected static function getEttersFinish(string $wordName): string
    {
        return '/s\\' . self::PRIVATE_SEGMENT . '/word:"' . $wordName . '"\(' . self::genFindParenthesis('arguments') . '/s|e\{';
    }

    protected static function genFindParenthesis(string $name = 'condition'): string
    {
        return self::genFind(')', '(', $name);
    }

    protected static function genFind(string $needle, string $skip = '', string $name = ''): string
    {
        $find = '/find:';
        $find .= self::encloseInString($needle);
        $find .= ':';
        if (!empty($skip)) {
            $find .= self::encloseInString($skip);
        }
        $find .= ':';
        if (!empty($name)) {
            $find .= self::encloseInString($name);
        }
        $find .= '>read';
        if (!empty($name)) {
            $find .= ':' . self::encloseInString($name);
        }
        $find .= '\\';
        return $find;
    }

    protected static function encloseInString(string $str): string
    {
        if (strpos($str, '"') === false) {
            return '"' . $str . '"';
        }
        return '`' . $str . '`';
    }
}
