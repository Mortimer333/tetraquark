<?php
use \Tetraquark\{Content, Validate, Str, Log};

return [
    "comments" => [
        "/" => [
            "/" => "\n",
            "*" => "*/"
        ],
    ],
    "prepare" => function(string $script)
    {
        return ' ' . trim($script);
    },
    "remove" => [
        "additional" => function(int &$i, Content &$content, string &$letter, ?string &$nextLetter, array $schemat): void
        {
            if (Validate::isWhitespace($letter) && is_null($nextLetter)) {
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
        "/s\if/s|e\(/find:')':'(':'condition'\)/s|e\{" => [
            "class" => "IfBlock",
            "_block" => [
                "end" => "}",
                "nested" => "{"
            ]
        ],
        "/s\class/s|e\/find:'{':null:'class_name'\\{" => [
            "class" => "ClassBlock",
            "_block" => [
                "end" => "}",
                "nested" => "{"
            ]
        ],
        "/s\continue/s|';'\\" => [
            "class" => "ContinueBlock"
        ],
    ],
    "methods" => [
        "find" => function (Content $content, string &$letter, int &$index, array &$data, string $needle, ?string $hayStarter = null, ?string $name = null)
        {
            $nestedHays = 0;
            for ($i=$index; $i < $content->getLength(); $i++) {
                $i = Str::skip($content->getLetter($i), $i, $content);
                $letter = $content->getLetter($i);
                if (!is_null($hayStarter) && $letter === $hayStarter) {
                    $nestedHays++;
                }

                if ($letter === $needle) {
                    if ($nestedHays > 0) {
                        $nestedHays--;
                        continue;
                    }
                    if (!is_null($name)) {
                        $data[$name] = trim($content->iSubStr($index, $i - 1));
                    }
                    $index = $i - 1;
                    return true;
                }
            }
            return false;
        },
        "s" => function (Content $content, string &$letter, int &$i)
        {
            return Validate::isWhitespace($letter);
        }
    ],
    "namespace" => "\Tetraquark\Block\\"
];
