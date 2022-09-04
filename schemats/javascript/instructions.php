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
    "/s|end\let/s\/varend\\" => [
        "class" => "LetVariableBlock"
    ],
    /* CONST */
    "/s|end\const/s\/varend\\" => [
        "class" => "ConstVariableBlock"
    ],
    /* VAR */
    '/s|end\var/s\/varend\\' => [
        "class" => "VarVariableBlock"
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
    /* ATTRIBUTE or VARIABLE ASSIGNMENT */
    '/word\/s|e\=' => [
        "class" => "AttributeBlock",
        "_block" => [
            "end" => "/varend\\"
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
    '/word\/s|e\(' => [
        "class" => "CallerBlock",
        "_block" => [
            "end" => ")",
            "nested" => "("
        ]
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
];
