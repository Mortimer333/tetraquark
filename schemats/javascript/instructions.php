<?php declare(strict_types=1);

require_once __DIR__ . '/landmark.php';

$arrayChainInstruction = [
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

$exportFromBlock = [
    "class" => "ExportFromBlock",
    "object" => true,
];
$exportAliasStringItemBlock = [
    "class" => "ExportAliasBlock",
    "string" => true,
];
$objectItemBlock = [
    "class" => "ObjectValueBlock",
    "_block" => [
        "end" => '/objectend\\',
        "include_end" => true,
    ],
];
$importAliasStringItemBlock = [
    "class" => "ImportAliasBlock",
    "string" => true,
];
$fromItemBlock = [
    "class" => "FromBlock"
];
$arrowMethod = [
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
foreach ($arrowMethod as $key => $value) {
    $arrowMethodAsync[$key] = array_merge($value, ['async' => true]);
}

return [
    /* SINGLE LINE COMMENT */
    "\/\//find:\n::'comment'\\" => [
        "class" => "SingleCommentBlock"
    ],
    /* MULTI LINE COMMENT */
    "\/*/find:'*/'::'comment'\\" => [
        "class" => "MultiCommentBlock"
    ],
    /* IF */
    '/s|end\if/s|e\(/find:")":"(":"condition">read:"condition"\\' => [
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
    ],
    /* CLASS DEFINITION */
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
    ],
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
    /* VARIABLE */
    '/s|end\/"#">isprivate|e\/word:"name"\\' => [
        "class" => "VariableInstanceBlock",
        "empty" => true,
        "_extend" => [
            '/s|e\\' => [
                "_extend" => [
                    '=/"!=">decrease\\' => [
                        "class" => "VariableInstanceBlock",
                        "replace" => true,
                        "_block" => [
                            "end" => "/varend\\",
                            "include_end" => true,
                        ]
                    ],
                    '/assignment\\=/"!=">decrease\\' => [
                        "class" => "VariableInstanceBlock",
                        "_block" => [
                            "end" => "/varend\\",
                            "include_end" => true,
                        ]
                    ],
                    '++' => [
                        "class" => "VariableInstanceBlock",
                        "type" => "addition",
                    ],
                    '--' => [
                        "class" => "VariableInstanceBlock",
                        "type" => "subtraction",
                    ]
                ]
            ]
        ]
    ],
    /* STATIC VARIABLE */
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
            '/s\/"#">isprivate|e\/word:"name"\\' => [
                "class" => "StaticVariableInstanceBlock",
                "empty" => true,
                "_extend" => [
                    '/s|e\\=' => [
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
    /* SPREAD VARIABLE */
    '.../s|e\/word:"name"\\' => [
        "class" => "VariableInstanceBlock",
        "spread" => true,
    ],
    /* ARRAY */
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
    ],
    /* SPREAD ARRAY */
    '.../s|e\[' => [
        "class" => "ArrayBlock",
        "spread" => true,
        "_block" => [
            "end" => "]",
            "nested" => "[",
        ]
    ],
    /* STRING ' */
    "'/strend:\"'\"\\" => JsLandmark::STRING_LANDMARK,
    /* STRING ` */
    '`/strend:"`":"template">templateliteral:"template">read:"template"\\' => [
        "class" => "StringBlock",
    ],
    /* STRING " */
    '"/strend:\'"\'\\' => [
        "class" => "StringBlock",
    ],
    /* COMMA */
    ',' => [
        "class" => "CommaBlock",
    ],
    /*
        ARROW FUNCTION
        Possible function syntaxes:
        - () => {}
        - x => {}
        - x => x + 1
        - (x) => x + 1
     */
    ...$arrowMethod,
    '/s|end\async/s|e\\' => [
        "_extend" => [
            ...$arrowMethodAsync
        ]
    ],
    /* KEYWORD */
    // Ended with new line
    '/s|end\/taken\/s|";"\\' => [
        "class" => "KeywordBlock"
    ],
    /* CALLER */
    '/s|end\/"#">isprivate|e\/word:"name"\/s|e\(' => [
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
    /* GETTER */
    '/s|end\get/s\/"#">isprivate|e\/word:"getter"\(/find:")":"(":"arguments">read:"arguments"\/s|e\{' => [
        "class" => "GetterClassMethodBlock",
        "_block" => [
            "end" => "}",
            "nested" => "{"
        ],
    ],
    /* SETTER */
    '/s|end\set/s\/"#">isprivate|e\/word:"setter"\(/find:")":"(":"arguments">read:"arguments"\/s|e\{' => [
        "class" => "SetterClassMethodBlock",
        "_block" => [
            "end" => "}",
            "nested" => "{"
        ],
    ],
    /* AYNC */
    '/s|end\async/s\/"#">isprivate|e\/word:"name"\(/find:")":"(":"arguments">read:"arguments"\/s|e\{' => [
        "class" => "AsyncClassMethodBlock",
        "_block" => [
            "end" => "}",
            "nested" => "{"
        ],
    ],
    /* STATIC GETTER */
    '/s|end\static/s\get/s\/"#">isprivate|e\/word:"getter"\(/find:")":"(":"arguments">read:"arguments"\/s|e\{' => [
        "class" => "StaticGetterClassMethodBlock",
        "_block" => [
            "end" => "}",
            "nested" => "{"
        ],
    ],
    /* STATIC SETTER */
    '/s|end\static/s\set/s\/"#">isprivate|e\/word:"setter"\(/find:")":"(":"arguments">read:"arguments"\/s|e\{' => [
        "class" => "StaticSetterClassMethodBlock",
        "_block" => [
            "end" => "}",
            "nested" => "{"
        ],
    ],
    /* STATIC AYNC */
    '/s|end\static/s\async/s\/"#">isprivate|e\/word:"name"\(/find:")":"(":"arguments">read:"arguments"\/s|e\{' => [
        "class" => "StaticAsyncClassMethodBlock",
        "_block" => [
            "end" => "}",
            "nested" => "{"
        ],
    ],
    /* TRY */
    '/s|end\try/s|e\{' => [
        "class" => "TryBlock",
        "_block" => [
            "end" => "}",
            "nested" => "{"
        ]
    ],
    /* CATCH */
    '/s|end\catch/s|e\(/find:")":"(":"exception">read:"exception"\/s|e\{' => [
        "class" => "CatchBlock",
        "_block" => [
            "end" => "}",
            "nested" => "{"
        ]
    ],
    /* FINALLY */
    '/s|end\finally/s|e\{' => [
        "class" => "FinallyBlock",
        "_block" => [
            "end" => "}",
            "nested" => "{"
        ]
    ],
    /* FIRST IN CHAIN */
    '/word:"first"\\' => [
        "_extend" => [
            '/"?">optionalchain|e\./"#">isprivate|e\/word:"second"\\' => [
                "class" => "ChainBlock",
                "first" => true,
                "_block" => [
                    "end" => '/varend\\',
                    "include_end" => true,
                ],
                "_extend" => [
                    '/s|e\(/find:")":"(":"values_two">read:"values_two"\\' => [
                        "class" => "ChainBlock",
                        "first_method" => false,
                        "second_method" => true,
                        "first" => true,
                        "_block" => [
                            "end" => '/varend\\',
                            "include_end" => true,
                        ],
                    ],
                    "/s|e\=" => [
                        "class" => "ChainBlock",
                        "first" => true,
                        "var" => true,
                        "_block" => [
                            "end" => '/varend\\',
                            "include_end" => true,
                        ],
                    ],
                    /* CHAIN (ARRAY ACCESS) */
                    ...$arrayChainInstruction,
                ]
            ],
            '(/find:")":"(":"values">read:"values"\/"?">optionalchain|e\./"#">isprivate|e\/word:"second"\\' => [
                "class" => "ChainBlock",
                "first" => true,
                "first_method" => true,
                "_block" => [
                    "end" => '/varend\\',
                    "include_end" => true,
                ],
                "_extend" => [
                    '/s|e\(/find:")":"(":"values_two">read:"values_two"\\' => [
                        "class" => "ChainBlock",
                        "first_method" => true,
                        "second_method" => true,
                        "first" => true,
                        "_block" => [
                            "end" => '/varend\\',
                            "include_end" => true,
                        ],
                    ],
                    '/s|e\=' => [
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
    /* NEXT IN CHAIN */
    '/"?">optionalchain|e\./"#">isprivate|e\/word\\' => [
        "class" => "SubChainBlock1",
        "_block" => [
            "end" => '/varend\\',
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
            ...$arrayChainInstruction,
        ]
    ],
    'this/".">decrease\\' => [
        "class" => "ThisBlock",
        "_block" => [
            "end" => '/varend\\',
            "include_end" => true,
        ],
    ],
    /* EQUAL */
    "==" => [
        "class" => "EqualBlock",
        "_extend" => [
            "=" => [
                "class" => "ExactBlock",
            ]
        ]
    ],
    /* UNEQUAL */
    "!=" => [
        "class" => "DifferentBlock",
        "_extend" => [
            "=" => [
                "class" => "DistinctBlock",
            ]
        ]
    ],
    /* DO WHILE */
    '/s|end\do/s|e\{' => [
        "class" => "DoWhileBlock",
        "_block" => [
            "skip" => '/s|end\do/s|e\{',
            "end" => '}/s|e\while/s|e\(/find:")":"(":"while">read:"while"\\',
        ]
    ],
    /* WHILE */
    '/s|end\while/s|e\(/find:")":"(":"condition">read:"condition"\/s|e\{' => [
        "class" => "WhileBlock",
        "_block" => [
            "skip" => '{',
            "end" => '}',
        ]
    ],
    /* SHORT WHILE */
    '/s|end\while/s|e\(/find:")":"(":"condition">read:"condition"\/nparenthesis>decrease\/varend\\' => [
        "class" => "ShortWhileBlock"
    ],
    /* ELSE / ELSE IF */
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
    /* FALSE */
    '/s|end\false' => [
        "class" => "FalseBlock"
    ],
    /* TRUE */
    '/s|end\true' => [
        "class" => "TrueBlock"
    ],
    /* FOR */
    '/s|end\for/s|e\(/find:")":"(":"condition">read:"condition"\/s|e\{' => [
        "class" => "ForBlock",
        "_block" => [
            "end" => "}",
            "nested" => "{"
        ]
    ],
    /* SHORT FOR */
    '/s|end\for/s|e\(/find:")":"(":"condition">read:"condition"\/nparenthesis>decrease\/varend\\' => [
        "class" => "ShortForBlock"
    ],
    /* FUNCTION */
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
    /* NEW INSTANCE */
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
    /* OBJECT */
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
    /* OBJECT ITEM */
    '/s|e|"{"\\' => [
        "_extend" => [
            "'/strend:`'`\/s|e\:" => $objectItemBlock,
            '`/strend:"`":"template">templateliteral\/s|e\:' => $objectItemBlock,
            '"/strend:`"`\/s|e\:' => $objectItemBlock,
            '/word:"name"\/s|e\:' => $objectItemBlock,
            '[/find:"]":"[":"key">read:"key"\/s|e\:' => ["key" => true, ...$objectItemBlock],
        ]
    ],
    /* SPREAD OBJECT */
    '.../s|e\{' => [
        "spread" => true,
        "class" => "ObjectBlock",
        "_block" => [
            "end" => "}",
            "skip" => "{",
        ],
    ],
    /* RETURN */
    '/s|end\return/s|symbol>decrease\\' => [
        "_block" => [
            "end" => '/varend\\',
            "include_end" => true,
        ],
        "class" => "ReturnBlock",
    ],
    /* SWITCH */
    '/s|end\switch/s|e\(/find:")":"(":"values">read:"values"\/s|e\{' => [
        "class" => "SwitchBlock",
        "_block" => [
            "end" => "}",
            "nested" => "{"
        ],
    ],
    /* SWITCH CASE */
    '/s|end\case/s\/word:"case"\/s|e\:' => [
        "class" => "SwitchCaseBlock",
        "_block" => [
            "end" => ['/case\\', 'break'],
            "nested" => '/s|end\case/s\/word:"case"\/s|e\:',
        ]
    ],
    /* SWITCH DEFAULT CASE */
    '/s|end\default/s|e\:' => [
        "class" => "SwitchDefaultCaseBlock",
        "_block" => [
            "end" => ['/case\\', 'break'],
            "nested" => '/s|end\default/s|e\:',
        ]
    ],
    /* SYMBOL */
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
    /* YELD */
    '/s|end\yield/"*">isgenerator|e\/s|symbol>decrease\\' => [
        "_block" => [
            "end" => '/varend\\',
            "include_end" => true,
        ],
        "class" => "YieldBlock",
    ],
    '(' => [
        "class" => "ScopeBlock",
        "_block" => [
            "end" => ")",
            "nested" => "(",
        ]
    ],
    /* IMPORT */
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
    /* EXPORT */
    '/s|end\/word:"name"\/s\as/s|e\\' => [
        "_extend" => [
            '`/strend:"`":"template">templateliteral\\' => $exportAliasStringItemBlock,
            '\'/strend:// Log ::\'// Log ::\\' => $exportAliasStringItemBlock,
            '"/strend:`"`\\' => $exportAliasStringItemBlock,
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
