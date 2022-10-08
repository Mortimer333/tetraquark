<?php

use Content\Utf8 as Content;

return [
    "comments" => [
        "/" => [
            "*" => "*/",
            "/" => "\n",
        ],
    ],
    "instructions" => [
        "test" => [
            "type" => "Test"
        ]
    ],
    "prepare" => [
        "missed" => function(string $missed): string
        {
            return trim($missed);
        }
    ],
];
