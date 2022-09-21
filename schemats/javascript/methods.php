<?php declare(strict_types=1);

use \Tetraquark\{Content, Validate, Str, Log};
use \Tetraquark\Model\CustomMethodEssentialsModel;

return [
    "case" =>  function (CustomMethodEssentialsModel $essentials): bool
    {
        $possibleCase = $essentials->getContent()->subStr($essentials->getI() + 1, 4);
        if ($possibleCase === 'case') {
            return true;
        }
        return false;
    },
    "decrease" => function (CustomMethodEssentialsModel $essentials, $var): void
    {
        $essentials->i--;
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
    "end" => function (CustomMethodEssentialsModel $essentials, $iter = 0): bool
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
    "varend" => function (CustomMethodEssentialsModel $essentials, bool $comma = true, $iter = 0)
    {
        if ($iter >= 50) {
            die('Stop');
        }

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

        $essentials->setI($essentials->getI());

        return true;
    },
    "varendNext" => function (CustomMethodEssentialsModel $essentials, $iter)
    {
        $essentials->setI($essentials->getI() + 1);
        return $essentials->getMethods()['varend']($essentials, $iter + 1);
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
    "symbol" => function (CustomMethodEssentialsModel $essentials): bool
    {
        return preg_match("/[\W]+/", $essentials->getLetter()) !== false;
    }
];
