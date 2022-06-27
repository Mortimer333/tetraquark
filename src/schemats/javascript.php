<?php
use \Tetraquark\{Content, Validate};

return [
    "comments" => [
        "/" => [
            "/" => "\n"
        ],
        "/" => [
            "*" => "*/"
        ]
    ],
    "remove" => [
        "additional" => function(int &$i, Content &$content, string &$letter, string &$nextLetter, array $schemat) {
            if ($letter === "\n" && Validate::isWhitespace($nextLetter)) {
                $content->remove($i + 1);
                $i--;
                return;
            }

            if ($letter === "\r" || (Validate::isWhitespace($letter) && Validate::isWhitespace($nextLetter))) {
                $content->remove($i);
                $i--;
            }
        }
    ]
];
