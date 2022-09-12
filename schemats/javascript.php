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
        "ends" => [
            "\n" => true,
            ";" => true,
            "}" => true, // @TODO test this one
            "," => true, // @TODO test this one
        ],
    ],
    "instructions" => require(__DIR__ . '/javascript/instructions.php'),
    "methods" => include(__DIR__ . '/javascript/methods.php'),
    "namespace" => "\Tetraquark\Block\\"
];
