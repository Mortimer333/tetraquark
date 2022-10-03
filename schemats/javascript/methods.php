<?php declare(strict_types=1);

use \Tetraquark\{Content, Validate, Str, Log};
use \Tetraquark\Model\CustomMethodEssentialsModel;

require_once __DIR__ . '/landmark.php';

class Helpers
{
    public static function checkIfValidVarEnd(CustomMethodEssentialsModel $essentials, int $i): bool
    {
        $content = $essentials->getContent();
        list($prevLetter, $prevPos) = Str::getPreviousLetter($i, $essentials->getContent());
        if (
            Validate::isOperator($prevLetter)
            && !Validate::isStringLandmark($prevLetter, '')
            && !Validate::isComment($prevPos, $content)
        ) {
            return false;
        }

        list($nextLetter, $nextPos) = Str::getNextLetter($i, $content);

        if (strlen($nextLetter) == 0) {
            // End of file
            return true;
        }

        if (
            Validate::isOperator($nextLetter, true)
            && !Validate::isStringLandmark($nextLetter, '')
            && !Validate::isComment($nextPos, $content)
        ) {
            return false;
        }

        list($previousWord) = Str::getPreviousWord($i, $content);
        if (Validate::isExtendingKeyWord($previousWord)) {
            return false;
        }

        list($nextWord) = Str::getNextWord($i, $content);
        if (Validate::isExtendingKeyWord($nextWord)) {
            return false;
        }

        return true;
    }

    public static function finishVarEnd(CustomMethodEssentialsModel $essentials, int $i, ?string $letter): void
    {
        $essentials->appendData(
            $essentials->getContent()->iSubStr($essentials->getI(), $i),
            "var"
        );
        $essentials->setI($i);
        $essentials->appendData($letter, "stop");
    }

    public static function getNextChain(CustomMethodEssentialsModel $essentials, int $pos): int
    {
        $content = $essentials->getContent();
        list($letter, $newPos) = Str::getNextLetter($pos, $content);

        if ($letter == '.') {
            list($nextWord, $wordPos) = Str::getNextWord($newPos + 1, $content, !Validate::isWhitespace($content->getLetter($newPos + 1)));
            return Helpers::getNextChain($essentials, $wordPos + 1);
        } elseif ($letter == "=") {
            $data = $essentials->getData();
            $essentials->getMethods()['varend']($essentials);
            $essentials->setData($data);
            return $essentials->getI();
        } elseif ($letter == "(") {
            $data = $essentials->getData();
            $essentials->setI($newPos + 1);
            $essentials->getMethods()['find']($essentials, ")", "(", "find");
            $essentials->setData($data);
            return Helpers::getNextChain($essentials, $essentials->getI() + 1);
        }

        return $pos;
    }
}


return [
    "consecutivecaller" => function (CustomMethodEssentialsModel $essentials): bool
    {
        $previous = $essentials->getContent()->getLetter($essentials->getI() - 2);
        if ($previous == ")") {
            return true;
        }
        return false;
    },
    "chainend" => function (CustomMethodEssentialsModel $essentials): bool
    {
        $start = $essentials->getI();
        $end = Helpers::getNextChain($essentials, $essentials->getI());
        if ($start != $end) {
            $i = $end;
        } else {
            $i = $end - 1;
        }
        $essentials->setI($i);
        return true;
    },
    "this" => function (CustomMethodEssentialsModel $essentials): bool
    {
        return $essentials->getContent()->subStr($essentials->getI(), 4) == 'this';
    },
    "number" => function (CustomMethodEssentialsModel $essentials): bool
    {
        if (is_numeric($essentials->getLetter())) {
            $end = $essentials->getContent()->getLength() - 1;
            for ($i=$essentials->getI() + 1; $i < $essentials->getContent()->getLength(); $i++) {
                $letter = $essentials->getContent()->getLetter($i);
                if (!is_numeric($letter) && $letter != '.') {
                    $end = $i - 1;
                    break;
                }
            }
            $essentials->appendData($essentials->getContent()->iSubStr($essentials->getI(), $end), "number");
            $essentials->setI($end);
            return true;
        }
        return false;
    },
    "templateliteral" => function (CustomMethodEssentialsModel $essentials, string $name = "template"): void
    {
        $string = $essentials->getData()[$name]
            ?? throw new \Exception("Couldn't find template literal in data with name: " . htmlentities($name));

        $pos = 0;
        $addEnd = true;
        $string = '"' . $string;
        while ($pos !== false) {
            $pos = strpos($string, '${', $pos);
            if ($pos !== false) {
                $start = $pos;
                $end = strpos($string, '}', $start + 2);
                if ($end === false) {
                    $pos = false;
                    $addEnd = false;
                } else {
                    $pre = substr($string, 0, $end);
                    $after = substr($string, $end + 1);
                    $string = $pre . ' "' . $after;
                    $pos = $end + 1;
                }
                $string[$start] = '"';
                $string[$start + 1] = ' ';

            }
        }
        if ($addEnd) {
            $string .= '"';
        }

        $essentials->appendData($string, $name);
    },
    "isprivate" => function (CustomMethodEssentialsModel $essentials): void
    {
        $essentials->appendData(true, 'private');
    },
    "isgenerator" => function (CustomMethodEssentialsModel $essentials): void
    {
        $essentials->appendData(true, 'generator');
    },
    "optionalchain" => function (CustomMethodEssentialsModel $essentials): void
    {
        $essentials->appendData(true, 'optional_chain');
    },
    "read" => function (CustomMethodEssentialsModel $essentials, string $valueName, ?string $name = null): void
    {
        if (is_null($name)) {
            $name = $valueName;
        }

        $data = $essentials->getData();
        if (!isset($data[$valueName])) {
            return;
        }

        $content = $data[$valueName];
        $comments = $essentials->reader->getComments();
        $essentials->reader->setComments([]);
        $blocks = $essentials->reader->read($content);
        $essentials->reader->setComments($comments);

        $essentials->appendData($blocks, $name);
    },
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
            // "/" => true,
        ];
        $res = !isset($skipped[$essentials->getLetter()])
            && (preg_match("/[\W]+/", $essentials->getLetter()) === 1)
            && !Validate::isStringLandmark($essentials->getLetter())
            && !Validate::isWhitespace($essentials->getLetter());
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
            'break' => true, 'instanceof' => true, // 'this' => true,
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
    "word" => function (CustomMethodEssentialsModel $essentials, string $name = "word", bool $varValidation = true): bool
    {
        list($word, $i) = Str::getNextWord($essentials->getI(), $essentials->getContent(), true);
        if (empty($word) || ($varValidation && !Validate::isJSValidVariable($word))) {
            return false;
        }

        $essentials->appendData($word, $name);

        $essentials->setI($i);
        return true;
    },
    "strend" => function (CustomMethodEssentialsModel $essentials, string $type, string $name = 'string'): bool
    {
        $i = Str::skip($type, $essentials->getI() - 1, $essentials->getContent());
        $essentials->appendData(
            $essentials->getContent()->iSubStr($essentials->getI(), $i - 2),
            $name
        );

        if ($essentials->getContent()->getLength() == $i + 1) {
            $essentials->setI($i);
        } else {
            $essentials->setI($i - 1);
        }
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
    // @POSSIBLE_PERFORMANCE_ISSUE
    "varend" => function (CustomMethodEssentialsModel $essentials, bool $comma = true)
    {
        $var   = $essentials->getData()['var'] ?? '';
        $stops = ["\n", ";"];
        if ($comma) {
            $stops[] = ",";
        }
        $findNext = [
            '{' => '}',
            '[' => ']',
            '(' => ')',
        ];

        $content = $essentials->getContent();
        $search  = false;
        $reader  = $essentials->getReader();

        for ($i = $essentials->getI(); $i < $content->getLength(); $i++) {
            $i = $reader->handleComment($essentials, $i);

            // Skip string
            $i = Str::skip($content->getLetter($i), $i, $content);
            $letter = $content->getLetter($i);

            if ($search) {
                if ($search['end'] == $letter) {
                    if ($search['skip_counter'] > 0) {
                        $search['skip_counter']--;
                        continue;
                    }
                    $search = null;
                    // If next letter after ), }, ] is not connector (means that there is not operation next)
                    // finish search for varend
                    list($nextLetter, $nextPos) = Str::getNextLetter($i + 1, $content);

                    if (!Validate::isOperator($nextLetter)) {
                        Helpers::finishVarEnd($essentials, $i, $letter);
                        return true;
                    }
                    continue;
                }

                if ($search['skip'] == $letter) {
                    $search['skip_counter']++;
                }
                continue;
            }

            if (isset($findNext[$letter])) {
                $search = [
                    "end" => $findNext[$letter],
                    "skip" => $letter,
                    "skip_counter" => 0
                ];
                continue;
            }

            if (
                $letter === ';'
                || ($comma && $letter === ',')
                || is_null($letter)
                || ($letter === "\n" && Helpers::checkIfValidVarEnd($essentials, $i))
            ) {
                Helpers::finishVarEnd($essentials, $i, $letter);
                return true;
            }
        }
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
        $reader  = $essentials->getReader();

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
            $i = $reader->handleComment($essentials, $i);

            // Skip strings
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
                $data[$name] = trim($content->iSubStr($index, $i - 1));
            }
            $index = $i;
            $letter = null;
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
