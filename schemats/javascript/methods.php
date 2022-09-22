<?php declare(strict_types=1);

use \Tetraquark\{Content, Validate, Str, Log};
use \Tetraquark\Model\CustomMethodEssentialsModel;

return [
    "objectend" => function (CustomMethodEssentialsModel $essentials): bool
    {
        $varend = [
            "method" => $essentials->getMethods()['varend'],
            "args" => [false]
        ];
        $end = [',' => true, '}' => true];
        $skip = [
            "{" => "}",
            "(" => ")",
            "[" => "]",

            // we skip does too as they can have let var1, var2, var3;
            " var "   => $varend,
            " var\n"  => $varend,
            "\nvar\n" => $varend,
            "\nvar "  => $varend,

            " let "   => $varend,
            " let\n"  => $varend,
            "\nlet\n" => $varend,
            "\nlet "  => $varend,

            " const "   => $varend,
            " const\n"  => $varend,
            "\nconst\n" => $varend,
            "\nconst "  => $varend,

            "\/\/" => "\n",
            "/*" => "*/",
        ];
        $data = $essentials->getData();
        $start = $essentials->getI();
        $searchables = array_merge(array_keys($skip), array_keys($end));
        while ($essentials->getI() + 1 < $essentials->getContent()->getLength()) {
            $essentials->getMethods()['find'](
                $essentials,
                $searchables
            );
            $key = $essentials->getData()['foundkey'];

            if (is_null($key) || ($end[$key] ?? false)) {
                break;
            }

            $search = $skip[$key];
            if (is_array($search)) {
                $search["method"]($essentials, ...$search["args"]);
            } else {
                $essentials->getMethods()['find']($essentials, $search, $key);
            }
            $essentials->i++;
        }

        // Restore data
        $essentials->setData($data);
        return true;
    },
    "nparenthesis" => function (CustomMethodEssentialsModel $essentials): bool
    {
        list($nextLetter, $nextPos) = Str::getNextLetter($essentials->getI(), $essentials->getContent());
        return $nextLetter !== "{";
    },
    "symbol" => function (CustomMethodEssentialsModel $essentials, string $name = "symbol"): bool
    {
        $skipped = [
            ";" => true,
            "}" => true,
            ")" => true,
            "/" => true,
        ];
        $res = !isset($skipped[$essentials->getLetter()]) && (preg_match("/[\W]+/", $essentials->getLetter()) === 1) && !Validate::isWhitespace($essentials->getLetter());
        if ($res) {
            $essentials->appendData($essentials->getLetter(), $name);
        }
        return $res;
    },
    "case" =>  function (CustomMethodEssentialsModel $essentials): bool
    {
        $possibleCase = $essentials->getContent()->subStr($essentials->getI() + 1, 4);
        if ($possibleCase === 'case') {
            return true;
        }
        return false;
    },
    "decrease" => function (CustomMethodEssentialsModel $essentials, int $amount = 1): void
    {
        $essentials->i -= $amount;
    },
    "assignment" => function (CustomMethodEssentialsModel $essentials): bool
    {
        $single = [
            '-' => true,
            '+' => true,
            '*' => true,
            '/' => true,
            '%' => true,
            '^' => true,
            '|' => true,
            '&' => true,
            '=' => true,
            '~' => true,
        ];

        $double = [
            '>>' => true,
            '<<' => true,
            '**' => true,
            '&&' => true,
            '||' => true,
            '??' => true,
        ];

        $triple = [
            '>>>' => true,
        ];

        $start = $essentials->getI();
        $content = $essentials->getContent();
        $first = $content->getLetter($start);
        $second = $content->getLetter($start + 1);
        $last  = $content->getLetter($start + 2);
        if ($triple[$first . $second . $last] ?? false) {
            $move = $start + 2;
            $symbol = $first . $second . $last;
        } elseif ($double[$second . $last] ?? false) {
            $move = $start + 1;
            $symbol = $second . $last;
        } elseif ($single[$last] ?? false) {
            $move = $start;
            $symbol = $last;
        } else {
            return false;
        }
        $essentials->setI($move);
        $essentials->appendData($symbol, 'assignment');
        return true;
    },
    "taken" => function (CustomMethodEssentialsModel $essentials): bool
    {
        $nonBlockKeywords = [
            'break' => true, 'instanceof' => true, 'this' => true,
            'typeof' => true,  'void' => true, 'continue' => true,
            'debugger' => true, 'with' => true, 'default' => true,
            'delete' => true, 'enum' => true, 'super' => true,
            'null' => true, 'undefined' => true, 'NaN' => true,
            'Infinity' => true,
        ];
        list($word, $i) = Str::getNextWord($essentials->getI(), $essentials->getContent(), true);

        $res = $nonBlockKeywords[$word] ?? false;

        if ($res) {
            $essentials->appendData($word, "keyword");
            $essentials->setI($i);
            return true;
        }

        return false;
    },
    "word" => function (CustomMethodEssentialsModel $essentials, string $name = "word"): bool
    {
        list($word, $i) = Str::getNextWord($essentials->getI(), $essentials->getContent(), true);
        if (!Validate::isJSValidVariable($word)) {
            return false;
        }

        $essentials->appendData($word, $name);

        $essentials->setI($i);
        return true;
    },
    "strend" => function (CustomMethodEssentialsModel $essentials, string $type): bool
    {
        $i = Str::skip($type, $essentials->getI(), $essentials->getContent());
        $essentials->setI($i);
        return true;
    },
    "end" => function (CustomMethodEssentialsModel $essentials): bool
    {
        $ends = [
            ";" => true,
            "/" => true,
        ];

        /** @var ?BlockModel */
        $previous = $essentials->getPrevious();
        // If previous was block
        if (!is_null($previous) && ($previous->getLandmark()['_block'] ?? false)) {
            $ends['}'] = true;
        }

        return $ends[$essentials->getLetter()] ?? false;
    },
    "varend" => function (CustomMethodEssentialsModel $essentials, bool $comma = true)
    {
        $var   = $essentials->getData()['var'] ?? '';
        $stops = ["\n", ";"];
        if ($comma) {
            $stops[] = ",";
        }
        $essentials->getMethods()['find']($essentials, $stops, null, 'var');

        $newVar  = $essentials->getData()['var'];
        $var     = $var . $newVar;
        $i       = $essentials->getI() + 1;
        $content = $essentials->getContent();

        $letter = $essentials->getLetter();

        if ($letter === ';' || $letter === ',') {
            $essentials->setI($i);
            $essentials->appendData($var, "var");
            $essentials->appendData($letter, "stop");
            return true;
        }

        $var .= $letter;
        $essentials->appendData($var, "var");
        if (is_null($letter)) {
            return true;
        }

        list($prevLetter, $prevPos) = Str::getPreviousLetter($essentials->getI(), $essentials->getContent());
        if (
            Validate::isOperator($prevLetter)
            && !Validate::isStringLandmark($prevLetter, '')
            && !Validate::isComment($prevPos, $content)
        ) {
            return $essentials->getMethods()['varendNext']($essentials);
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
            return $essentials->getMethods()['varendNext']($essentials);
        }

        list($previousWord) = Str::getPreviousWord($i, $content);
        if (Validate::isExtendingKeyWord($previousWord)) {
            return $essentials->getMethods()['varendNext']($essentials);
        }

        list($nextWord) = Str::getNextWord($i, $content);
        if (Validate::isExtendingKeyWord($nextWord)) {
            return $essentials->getMethods()['varendNext']($essentials);
        }

        return true;
    },
    "varendNext" => function (CustomMethodEssentialsModel $essentials): bool
    {
        $essentials->setI($essentials->getI() + 1);
        return $essentials->getMethods()['varend']($essentials);
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
        // @POTENTIAL_PREFORMANCE_ISSUE
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
                    $data["foundkey"] = $key;
                    if (!is_null($name)) {
                        $data[$name] = trim($content->iSubStr($index, $i - $straw['len']));
                    }
                    $index = $i;
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
            $data["foundkey"] = null;
            if (!is_null($name)) {
                $data[$name] = trim($content->iSubStr($index, $i - $straw['len'] - 1));
            }
            $index = $i - 1;
        }

        $essentials->setLetter($letter);
        $essentials->setI($index);
        $essentials->setData($data);

        return $res;
    },
    "s" => function (CustomMethodEssentialsModel $essentials): bool
    {
        $letter = $essentials->getLetter();
        if (!Validate::isWhitespace($letter ?? '')) {
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
];
