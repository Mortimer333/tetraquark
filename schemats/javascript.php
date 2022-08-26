<?php
use \Tetraquark\{Content, Validate, Str, Log, Model\CustomMethodEssentialsModel};

return [
    "comments" => [
        "/" => [
            "/" => "\n",
            "*" => "*/"
        ],
    ],
    "prepare" => function(Content $content)
    {
        return $content->trim()->prependArrayContent([' ']);
    },
    "remove" => [
        "comments" => false,
    //     "additional" => function(int &$i, Content &$content, string &$letter, ?string &$nextLetter, array $schemat): void
    //     {
    //         if (Validate::isWhitespace($letter) && is_null($nextLetter)) {
    //             $content->remove($i);
    //             $i--;
    //             return;
    //         }
    //
    //         if ($letter === "\n" && Validate::isWhitespace($nextLetter)) {
    //             $content->remove($i + 1);
    //             $i--;
    //             return;
    //         }
    //
    //         if ($letter === "\r" || (Validate::isWhitespace($letter) && Validate::isWhitespace($nextLetter))) {
    //             $content->remove($i);
    //             $i--;
    //         }
    //     }
    ],
    "instructions" => [
        /* SINGLE LINE COMMENT */
        "\/\//find:\n:null:'comment'\\" => [
            "class" => "SingleCommentBlock"
        ],
        /* MULTI LINE COMMENT */
        "\/*/find:'*/':null:'comment'\\" => [
            "class" => "MultiCommentBlock"
        ],
        /* IF */
        "/s\if/s|e\(/find:')':'(':'condition'\)/s|e\{" => [
            "class" => "IfBlock",
            "_block" => [
                "end" => "}",
                "nested" => "{"
            ]
        ],
        /* SHORT IF */
        "/s\if/s|e\(/find:')':'(':'condition'\)/s|e\/short\\" => [
            "class" => "ShortIfBlock",
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
        "/s\continue/n|';'\\" => [
            "class" => "ContinueBlock"
        ],
    ],
    "methods" => [
        "short" => function (CustomMethodEssentialsModel $essentials)
        {
            return false;
        },
        "find" => function (CustomMethodEssentialsModel $essentials, string $needle, ?string $hayStarter = null, ?string $name = null): bool
        {
            $content = $essentials->getContent();
            $letter  = $essentials->getLetter();
            $index   = $essentials->getI();
            $data    = $essentials->getData();
            $needleLen = strlen($needle);
            $searchAr = [];

            $nestedHays = 0;
            $res = false;
            for ($i=$index; $i < $content->getLength(); $i++) {
                $i = Str::skip($content->getLetter($i), $i, $content);
                $letter = $content->getLetter($i);

                $searchAr[] = $letter;
                if (sizeof($searchAr) > $needleLen) {
                    array_shift($searchAr);
                }

                if (!is_null($hayStarter) && $letter === $hayStarter) {
                    $nestedHays++;
                }

                if (implode('', $searchAr) === $needle) {
                    if ($nestedHays > 0) {
                        $nestedHays--;
                        continue;
                    }
                    if (!is_null($name)) {
                        $data[$name] = trim($content->iSubStr($index, $i - $needleLen));
                    }
                    $index = $i - 1;
                    $res = true;
                    break;
                }
            }

            $essentials->setLetter($letter);
            $essentials->setI($index);
            $essentials->setData($data);

            return $res;
        },
        "s" => function (CustomMethodEssentialsModel $essentials): bool
        {
            if (!Validate::isWhitespace($essentials->getLetter())) {
                return false;
            }
            list($letter, $pos) = Str::getNextLetter($essentials->getI(), $essentials->getContent());
            if (strlen($letter) !== 0) {
                $essentials->setI($pos - 1);
            }
            return true;
        },
        "n" => function (CustomMethodEssentialsModel $essentials): bool
        {
            return $essentials->getLetter() === "\n" || $essentials->getLetter() === "\r";
        },
    ],
    "namespace" => "\Tetraquark\Block\\"
];
