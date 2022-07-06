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
        "additional" => function(int &$i, Content &$content, string &$letter, ?string &$nextLetter, array $schemat): void
        {
            if (Validate::isWhitespace($letter) && (is_null($nextLetter) || $i == 0)) {
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
    ],
    "instructions" => [
        "if/s|e\(/match:(:)>condition\)/s|e\{" => "IfBlock",
        "class/match: :(>class\\" => "ClassBlock",
        "continue/s\\" => "ContinueBlock",
    ],
    "matchers" => [
        "match" => function (int &$i, Content $instr, string &$letter, array $schemat)
        {
            return "condition";
        }
    ],
    "namespace" => "\Tetraquark\Block\\"
];
