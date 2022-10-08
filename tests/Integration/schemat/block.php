<?php

use Content\Utf8 as Content;

return [
    "instructions" => [
        'if/s|e\(/find:")":"(":"condition"\/s|e\{' => [
            "type" => "If",
            "_block" => [
                "end" => "}",
                "nested" => "{",
            ],
        ],
        'var/s\/find:"="\\' => [
            "type" => "Var",
            "_block" => [
                "end" => [";", "\n"],
                "nested" => ['var/s\/find:"="\\', 'let/s\/find:"="\\'],
                "include_end" => true,
            ],
        ],
    ],
    "prepare" => [
        "missed" => function(string $missed): string
        {
            return trim($missed);
        }
    ],
];
