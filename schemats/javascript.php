<?php
use \Tetraquark\{Content, Validate, Str, Log};
use \Tetraquark\Model\CustomMethodEssentialsModel;

return [
    "comments" => [
        "/" => [
            "/" => "\n",
            "*" => "*/"
        ],
    ],
    "prepare" => function(Content $content): Content
    {
        return $content->trim()->prependArrayContent([' ']);
    },
    "shared" => [
        "ends" => ["\n", ";",],
    ],
    "instructions" => require(__DIR__ . '/javascript/instructions.php'),
    "methods" => include(__DIR__ . '/javascript/methods.php'),
    "namespace" => "\Tetraquark\Block\\"
];
