<?php declare(strict_types=1);

$arrowMethod = [
    '(/find:")":"(":"condition"\/s|e\=>/s|e\{' => [
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
    '(/find:")":"(":"condition"\/s|e\=>/s|e\/"!{"\/varend\\' => [
        "class" => "ArrowMethodBlock",
        "parenthesis" => true,
        "block" => false,
    ],
    '/word\/s|e\=>/s|e\/"!{"\/varend\\' => [
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
    "/s|end\if/s|e\(/find:')':'(':'condition'\/s|e\{" => [
        "class" => "IfBlock",
        "_block" => [
            "end" => "}",
            "nested" => "{"
        ]
    ],
    /* SHORT IF */
    "/s|end\if/s|e\(/find:')':'(':'condition'\/s|e\/'!{'\/varend\\" => [
        "class" => "ShortIfBlock"
    ],
    /* CLASS DEFINITION */
    "/s|end\class/s|e\/find:'{'::'class_name'\\" => [
        "class" => "ClassBlock",
        "_block" => [
            "end" => "}",
            "nested" => "{"
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
    '/s|end\/word:"name"\\' => [
        "class" => "VariableInstanceBlock",
        "empty" => true,
        "_extend" => [
            '/s|e\\' => [
                "_extend" => [
                    '=' => [
                        "class" => "VariableInstanceBlock",
                        "replace" => true,
                        "_block" => [
                            "end" => "/varend\\",
                            "include_end" => true,
                        ]
                    ],
                    '/assignment\\=' => [
                        "class" => "VariableInstanceBlock",
                        "_block" => [
                            "end" => "/varend\\",
                            "include_end" => true,
                        ]
                    ],
                    '++/varend\\' => [
                        "class" => "VariableInstanceBlock",
                        "type" => "addition",
                    ],
                    '--/varend\\' => [
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
            '/s\/word:"name"\\' => [
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
    "'/strend:\"'\"\\" => [
        "class" => "StringBlock",
    ],
    /* STRING ` */
    "`/strend:\"`\"\\" => [
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
    // Ended with ;
    '/s|end\/taken\/e|s\;' => [
        "class" => "KeywordBlock"
    ],
    // Ended with new line
    '/s|end\/taken\/s\\' => [
        "class" => "KeywordBlock"
    ],
    /* CALLER */
    '/s|end\/word:"name"\/s|e\(' => [
        "class" => "CallerBlock",
        "_block" => [
            "end" => ")",
            "nested" => "("
        ],
        "_extend" => [
            /* CLASS METHOD */
            '/find:")":"(":"arguments"\/s|e\{' => [
                "class" => "ClassMethodBlock",
                "_block" => [
                    "end" => "}",
                    "nested" => "{"
                ],
            ],
        ],
    ],
    /* GETTER */
    '/s|end\get/s\/word:"getter"\(/find:")":"(":"arguments"\/s|e\{' => [
        "class" => "GetterClassMethodBlock",
        "_block" => [
            "end" => "}",
            "nested" => "{"
        ],
    ],
    /* SETTER */
    '/s|end\set/s\/word:"setter"\(/find:")":"(":"arguments"\/s|e\{' => [
        "class" => "SetterClassMethodBlock",
        "_block" => [
            "end" => "}",
            "nested" => "{"
        ],
    ],
    /* AYNC */
    '/s|end\async/s\/word:"name"\(/find:")":"(":"arguments"\/s|e\{' => [
        "class" => "AsyncClassMethodBlock",
        "_block" => [
            "end" => "}",
            "nested" => "{"
        ],
    ],
    /* STATIC GETTER */
    '/s|end\static/s\get/s\/word:"getter"\(/find:")":"(":"arguments"\/s|e\{' => [
        "class" => "StaticGetterClassMethodBlock",
        "_block" => [
            "end" => "}",
            "nested" => "{"
        ],
    ],
    /* STATIC SETTER */
    '/s|end\static/s\set/s\/word:"setter"\(/find:")":"(":"arguments"\/s|e\{' => [
        "class" => "StaticSetterClassMethodBlock",
        "_block" => [
            "end" => "}",
            "nested" => "{"
        ],
    ],
    /* STATIC AYNC */
    '/s|end\static/s\async/s\/word:"name"\(/find:")":"(":"arguments"\/s|e\{' => [
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
    '/s|end\catch/s|e\(/find:")":"(":"exception"\/s|e\{' => [
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
    '/s|end\/word:"first"\\' => [
        "_extend" => [
            './word:"second"\\' => [
                "class" => "ChainBlock",
                "first" => true,
                "_block" => [
                    "end" => '/varend\\',
                    "include_end" => true,
                ],
                "_extend" => [
                    '/s|e\(/find:")":"(":"values_two"\\' => [
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
                    ]
                ]
            ],
            '(/find:")":"(":"values"\./word:"second"\\' => [
                "class" => "ChainBlock",
                "first" => true,
                "first_method" => true,
                "_block" => [
                    "end" => '/varend\\',
                    "include_end" => true,
                ],
                "_extend" => [
                    '/s|e\(/find:")":"(":"values_two"\\' => [
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
            ]

        ]
    ],
    /* NEXT IN CHAIN */
    './word\\' => [
        "class" => "SubChainBlock1",
        "_block" => [
            "end" => '/varend\\',
            "include_end" => true,
        ],
        "_extend" => [
            '/s|e\=' => [
                "class" => "SubChainBlock2",
                "var" => true,
                "_block" => [
                    "end" => '/varend\\',
                    "include_end" => true,
                ],
            ],
            '/s|e\(/find:")":"(":"values"\\' => [
                "class" => "SubChainBlock3",
                "method" => true,
            ]
        ]
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
            "end" => '}/s|e\while/s|e\(/find:")":"(":"while"\\',
        ]
    ],
    /* WHILE */
    '/s|end\while/s|e\(/find:")":"(":"while"\/s|e\{' => [
        "class" => "WhileBlock",
        "_block" => [
            "skip" => '{',
            "end" => '}',
        ]
    ],
    /* ELSE / ELSE IF */
    '/s|"}"\else' => [
        "_extend" => [
            "/s|e\{" => [
                "class" => "ElseBlock",
                "_block" => [
                    "skip" => '{',
                    "end" => '}',
                ]
            ],
            '/s\if/s|e\(/find:")":"(":"values"\/s|e\{' => [
                "class" => "ElseIfBlock",
                "_block" => [
                    "skip" => '{',
                    "end" => '}',
                ]
            ]
        ]
    ],
    /* NEW INSTANCE */
    '/s|end\new/s\/word\\' => [
        '_extend' => [
            '(/find:")":"(":"values"\\' => [
                "class" => "ClassInstanceBlock",
                "parenthesis" => true,
            ],
        ],
        "class" => "ClassInstanceBlock",
    ],
    /* OBJECT */
    '{' => [
        "_block" => [
            "end" => "}",
            "skip" => "{",
        ],
        "class" => "ObjectBlock",
    ],
    ':' => [
        "class" => "ObjectSeperatorBlock",
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
    '/s|end\return/s|symbol>decrease:"a"\\' => [
        "_block" => [
            "end" => '/varend\\',
            "include_end" => true,
        ],
        "class" => "ReturnBlock",
    ],
    /* SWITCH */
    '/s|end\switch/s|e\(/find:")":"(":"switch"\/s|e\{' => [
        "class" => "SwitchBlock",
        "_block" => [
            "skip" => '{',
            "end" => '}',
        ]
    ],
    '/s|end\case/s\/word\:' => [
        "class" => "SwitchCaseBlock",
        
    ]
];
