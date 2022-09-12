<?php declare(strict_types=1);

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
    /* ARRAY */
    "[" => [
        "class" => "ArrayBlock",
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
    '/word:"name"\/s|e\(' => [
        "class" => "CallerBlock",
        "_block" => [
            "end" => ")",
            "nested" => "("
        ],
        "_extend" => [
            /* CLASS METHOD */
            '/find:")":"(":"arguments"\\/s|e\{' => [
                "class" => "ClassMethodBlock",
                "_block" => [
                    "end" => "}",
                    "nested" => "{"
                ],
            ],
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
    "==" => [
        "class" => "EqualBlock",
        "_extend" => [
            "=" => [
                "class" => "ExactBlock",
            ]
        ]
    ],
    "!=" => [
        "class" => "DifferentBlock",
        "_extend" => [
            "=" => [
                "class" => "DistinctBlock",
            ]
        ]
    ],
    '/s|end\do/s|e\{' => [
        "class" => "DoWhileBlock",
        "_block" => [
            "skip" => '/s|end\do/s|e\{',
            "end" => '}/s|e\while/s|e\(/find:")":"(":"while"\\',
        ]
    ],
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

];
