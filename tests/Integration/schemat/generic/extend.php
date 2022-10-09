<?php

return [
    "instructions" => [
        '/s|e\\' => [
            "_extend" => [
                'chain' => [
                    "type" => "Chain",
                    "_extend" => [
                        "/s|e\./s|e\chain" => [
                            "type" => "DotChain",
                        ],
                        "/s|e\->/s|e\chain" => [
                            "type" => "ArrowChain",
                        ]
                    ]
                ],
            ]
        ],
    ]
];
