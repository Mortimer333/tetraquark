<?php

use Tetraquark\Model\CustomMethodEssentialsModel;
use Content\Utf8 as Content;

return [
    "instructions" => [
        '/s|e\console/s|e\./s|e\log/s|e\(/find:")":"(">addtest\\' => [
            "type" => "ConsoleLog",
        ],
    ],
    "methods" => [
        "addtest" => function (CustomMethodEssentialsModel $essentials): void
        {
            $essentials->appendData(true, 'test');
        },
    ]
];
