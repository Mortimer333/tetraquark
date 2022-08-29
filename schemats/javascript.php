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
        "\/\//find:\n::'comment'\\" => [
            "class" => "SingleCommentBlock"
        ],
        /* MULTI LINE COMMENT */
        "\/*/find:'*/'::'comment'\\" => [
            "class" => "MultiCommentBlock"
        ],
        /* IF */
        "/s|end\if/s|e\(/find:')':'(':'condition'\)/s|e\{" => [
            "class" => "IfBlock",
            "_block" => [
                "end" => "}",
                "nested" => "{"
            ]
        ],
        /* SHORT IF */
        "/s|end\if/s|e\(/find:')':'(':'condition'\)/s|e\/short\\" => [
            "class" => "ShortIfBlock",
            "_block" => [
                "end" => "}",
                "nested" => "{"
            ]
        ],
        /* CLASS DEFINITION */
        "/s|end\class/s|e\/find:'{'::'class_name'\\{" => [
            "class" => "ClassBlock",
            "_block" => [
                "end" => "}",
                "nested" => "{"
            ]
        ],
        /* CONTINUE */
        "/s|end\continue/n|';'\\" => [
            "class" => "ContinueBlock"
        ],
        /* LET */
        "/s|end\let/s\/varend\\" => [
            "class" => "LetVariableBlock"
        ],
        /* CONST */
        "/s|end\const/s\/varend\\" => [
            "class" => "LetVariableBlock"
        ],
        /* VAR */
        "/s|end\var/s\/varend\\" => [
            "class" => "LetVariableBlock"
        ],
    ],
    "methods" => [
        "end" => function (CustomMethodEssentialsModel $essentials, $iter = 0): bool
        {
            $ends = [
                ";" => true,
            ];
            $method = [
                "}" => true,
            ];

            return $ends[$essentials->getLetter()] ?? false;
        },
        "varend" => function (CustomMethodEssentialsModel $essentials, $iter = 0)
        {
            if ($iter >= 50) {
                die('Stop');
            }

            $var     = $essentials->getData()['var'] ?? '';

            $essentials->getMethods()['find']($essentials, ["\n", ";"], null, 'var');

            $newVar  = $essentials->getData()['var'];
            $essentials->setData(["var" => $var . $newVar . $essentials->getLetter()]);

            $essentials->setI($essentials->getI() + 1);

            $i       = $essentials->getI();
            $content = $essentials->getContent();


            if ($essentials->getLetter() === ';') {
                return true;
            }

            list($prevLetter, $prevPos) = Str::getPreviousLetter($essentials->getI(), $essentials->getContent());
            if (
                Validate::isOperator($prevLetter)
                && !Validate::isStringLandmark($prevLetter, '')
                && !Validate::isComment($prevPos, $content)
            ) {
                return $essentials->getMethods()['varendNext']($essentials, $iter);
            }

            list($nextLetter, $nextPos) = Str::getNextLetter($i, $content);

            if (strlen($nextLetter) == 0) {
                // End of file
                return true;
            }

            if (
                Validate::isOperator($nextLetter)
                && !Validate::isStringLandmark($nextLetter, '')
                && !Validate::isComment($nextPos, $content)
            ) {
                return $essentials->getMethods()['varendNext']($essentials, $iter);
            }

            list($previousWord) = Str::getPreviousWord($i, $content);
            if (Validate::isExtendingKeyWord($previousWord)) {
                return $essentials->getMethods()['varendNext']($essentials, $iter);
            }

            list($nextWord) = Str::getNextWord($i, $content);
            if (Validate::isExtendingKeyWord($nextWord)) {
                return $essentials->getMethods()['varendNext']($essentials, $iter);
            }

            return true;
        },
        "varendNext" => function (CustomMethodEssentialsModel $essentials, $iter)
        {
            $essentials->setI($essentials->getI() + 1);
            return $essentials->getMethods()['varend']($essentials, $iter + 1);
        },
        "short" => function (CustomMethodEssentialsModel $essentials)
        {
            return false;
        },
        "find" => function (CustomMethodEssentialsModel $essentials, string|array $needle, null|array|string $hayStarter = null, ?string $name = null): bool
        {
            $content = $essentials->getContent();
            $letter  = $essentials->getLetter();
            $index   = $essentials->getI();
            $data    = $essentials->getData();

            if (is_string($needle)) {
                $needle = [$needle];
            }

            if (empty($needle)) {
                throw new Exception("Needle can't be empty", 400);
            }

            $tmpNeedle = [];
            foreach ($needle as $value) {
                $heyStarterAr = [];
                if (is_array($hayStarter)) {
                    $heyStarterItem = $hayStarter[$value] ?? null;
                    if (is_string($heyStarterItem)) {
                        $heyStarterAr[$heyStarterItem] = true;
                    } elseif (is_array($heyStarterItem)) {
                        foreach ($heyStarterItem as $starterNeedle) {
                            $heyStarterAr[$starterNeedle] = true;
                        }
                    }
                } elseif (is_string($hayStarter)) {
                    $heyStarterAr[$hayStarter] = true;
                }

                $tmpNeedle[$value] = [
                    "needle" => $value,
                    "len" => mb_strlen($value),
                    "haystack" => [],
                    "hayStarter" => $heyStarterAr,
                    "skip" => 0,
                ];
            }
            $needle = $tmpNeedle;

            $nestedHays = 0;
            $res = false;
            for ($i=$index; $i < $content->getLength(); $i++) {
                $i = Str::skip($content->getLetter($i), $i, $content);
                $letter = $content->getLetter($i);
                foreach ($needle as $key => &$straw) {
                    $straw['haystack'][] = $letter;

                    if (sizeof($straw['haystack']) > $straw['len']) {
                        array_shift($straw['haystack']);
                    }

                    $posNeedle = implode('', $straw['haystack']);

                    if ($posNeedle === $key) {
                        if ($straw['skip'] > 0) {
                            $straw['skip']--;
                            continue 2;
                        }
                        if (!is_null($name)) {
                            $data[$name] = trim($content->iSubStr($index, $i - $straw['len']));
                        }
                        $index = $i - 1;
                        $res = true;
                        break 2;
                    }

                    if ($straw["hayStarter"][$posNeedle] ?? false) {
                        $straw['skip']++;
                        continue;
                    }
                }
            }

            if (!$res) {
                $index = $i - 1;
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
