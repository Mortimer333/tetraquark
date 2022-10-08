<?php

use Content\Utf8 as Content;

return [
    "comments" => [
        "/" => [
            "/" => "\n",
            "*" => "*/"
        ],
    ],
    "prepare" => [
        "content" => function(Content $content): Content
        {
            return $content->prependArrayContent([' ']);
        },
        "missed" => function(string $missed): string
        {
            return trim(rtrim(trim($missed), ';'));
        }
    ],
    "shared" => [
        "ends" => [
            "\n" => true,
            ";" => true,
            "}" => true, // @TODO test this one
            "," => true, // @TODO test this one
            ")" => true,
        ],
    ],
    "instructions" => require(__DIR__ . '/javascript/instructions.php'),
    "methods" => require(__DIR__ . '/javascript/methods.php'),
];
