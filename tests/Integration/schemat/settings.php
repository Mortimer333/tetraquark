<?php

use Content\Utf8 as Content;

return [
    "shared" => [
        "ends" => [
            ";" => true
        ]
    ],
    "comments" => [
        "/" => [
            "*" => "*/"
        ],
    ],
    "remove" => [
        "comments" => true,
    ],
    "instructions" => [
        "/s\debugger" => [
            "type" => "Debugger"
        ]
    ],
    "prepare" => [
        "content" => function(Content $content): Content
        {
            return $content->prependArrayContent([' ']);
        },
        "missed" => function(string $missed): string
        {
            return trim($missed);
        }
    ],
];
