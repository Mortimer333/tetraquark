<?php
use \Tetraquark\{Content, Validate};

return [
    "comments" => [
        "/" => [
            "/" => "\n",
            "*" => "*/"
        ],
    ],
    "remove" => [
        "additional" => function(int &$i, Content &$content, string &$letter, ?string &$nextLetter, array $schemat) {
            if (Validate::isWhitespace($letter) && is_null($nextLetter)) {
                $content->remove($i);
                $i--;
                return;
            }

            if (Validate::isWhitespace($letter) && $i == 0) {
                $content->remove($i);
                $i--;
                return;
            }

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
