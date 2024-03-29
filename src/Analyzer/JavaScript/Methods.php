<?php declare(strict_types=1);

namespace Tetraquark\Analyzer\JavaScript;

use Orator\Log;
use Content\Utf8 as Content;
use Tetraquark\{Str, Validate};
use Tetraquark\Analyzer\JavaScript\Validate as JsValidate;
use Tetraquark\Analyzer\JavaScript\Util\Helper;
use Tetraquark\Model\{CustomMethodEssentialsModel, Block\BlockModel};

abstract class Methods
{
    public static function get(array $settings = []): array
    {
        $functions = self::getDefinitions();

        foreach ($functions as $key => $value) {
            $functions[$key] = fn (...$args) => self::$value(...$args);
        }
        return $functions;
    }

    public static function getDefinitions(): array
    {
        return [
            "n" => "isNewLine",
            "end" => "end",
            "word" => "word",
            "this" => "this",
            "case" => "case",
            "taken" => "taken",
            "number" => "number",
            "symbol" => "symbol",
            "strend" => "strEnd",
            "varend" => "varEnd",
            "getnext" => "getNextInstruction",
            "chainend" => "chainEnd",
            "decrease" => "decrease",
            "isprivate" => "isPrivate",
            "objectend" => "objectEnd",
            "assignment" => "assignment",
            "isgenerator" => "isGenerator",
            "nparenthesis" => "notParenthesis",
            "optionalchain" => "optionalChain",
            "templateliteral" => "templateLiteral",
            "consecutivecaller" => "consecutiveCaller",
            "parentischainable" => "parentIsChainable", // @TODO Unit tests
            // @DEPRICATED
            // "varendNext" => function (CustomMethodEssentialsModel $essentials): bool
            // {
            //     $essentials->setI($essentials->getI() + 1);
            //     return $essentials->getMethods()['varend']($essentials);
            // },
        ];
    }

    public static function consecutiveCaller(CustomMethodEssentialsModel $essentials): bool
    {
        $i = $essentials->getI() - 2;
        if (is_null($essentials->getContent()->getLetter($i))) {
            return false;
        }

        list($previous) = Str::getPreviousLetter($i, $essentials->getContent());
        if ($previous == ")") {
            return true;
        }
        return false;
    }

    /**
     * @codeCoverageIgnore
     * Its covered by Helper::getNextChain
     */
    public static function chainEnd(CustomMethodEssentialsModel $essentials): bool
    {
        $start = $essentials->getI();
        $end = Helper::getNextChain($essentials, $essentials->getI());
        // if ($start != $end) {
        //     $i = $end;
        // } else {
        //     $i = $end - 1;
        // }
        $essentials->setI($end);
        return true;
    }

    public static function this(CustomMethodEssentialsModel $essentials): bool
    {
        return $essentials->getContent()->subStr($essentials->getI(), 4) == 'this';
    }

    public static function number(CustomMethodEssentialsModel $essentials, string $name = "number"): bool
    {
        $content = $essentials->getContent();
        $letter = $essentials->getLetter();
        if (is_numeric($letter) || $letter == '.') {
            $end = $content->getLength() - 1;
            for ($i=$essentials->getI() + 1; $i < $content->getLength(); $i++) {
                $letter = $content->getLetter($i);
                if (!is_numeric($letter) && $letter != '.') {
                    $end = $i - 1;
                    break;
                }
            }
            if ($end == $essentials->getI() && $essentials->getLetter() == '.') {
                return false;
            }
            // If number is constructed like this `2.` then it's not a number
            if (is_numeric($content->getLetter($end - 1)) && $content->getLetter($end) === '.') {
                $end--;
            }
            $essentials->appendData($content->iSubStr($essentials->getI(), $end), $name);
            $essentials->setI($end);
            return true;
        }
        return false;
    }

    public static function templateLiteral(CustomMethodEssentialsModel $essentials, string $name = "template"): void
    {
        $string = $essentials->getData()[$name]
            ?? throw new \Exception("Couldn't find template literal in data with name: " . htmlentities($name));

        $pos = 0;
        $addEnd = true;
        $string = str_replace('"', '\"', $string);
        $content = new Content('"' . $string);
        while ($pos !== false) {
            $pos = $content->find('${', $pos);
            if ($pos !== false) {
                $start = $pos;
                list($end, $foundkey) = Str::skipBlock('}', $pos + 2, $content, "{");
                $content->splice($start - 1, 1, ['"']);
                $content->splice($start, 1, [' ']);
                if (is_null($foundkey)) {
                    $pos = false;
                    $addEnd = false;
                } else {
                    $pre = $content->subStr(0, $end - 1);
                    $after = $content->subStr($end);
                    $content->splice($end - 1, 1, [' "']);
                    $pos = $end + 1;
                }
            }
        }
        if ($addEnd) {
            $content->apendArrayContent(['"']);
        }
        $essentials->appendData($content . '', $name);
    }

    /**
     * @codeCoverageIgnore
     */
    public static function isPrivate(CustomMethodEssentialsModel $essentials): void
    {
        $essentials->appendData(true, 'private');
    }

    /**
     * @codeCoverageIgnore
     */
    public static function isGenerator(CustomMethodEssentialsModel $essentials): void
    {
        $essentials->appendData(true, 'generator');
    }

    /**
     * @codeCoverageIgnore
     */
    public static function optionalChain(CustomMethodEssentialsModel $essentials): void
    {
        $essentials->appendData(true, 'optional_chain');
    }

    public static function objectEnd(CustomMethodEssentialsModel $essentials): bool
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

            "//" => "\n",
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
            $key = $essentials->getData()['foundkey'] ?? null;
            if (is_null($key) || ($end[$key] ?? false)) {
                break;
            }

            $search = $skip[$key];
            if (is_array($search)) {
                $search["method"]($essentials, ...$search["args"]);
            } else {
                $essentials->i++;
                $essentials->getMethods()['find']($essentials, $search, $key);
            }
            $essentials->i++;
        }
        $lastLetter = $essentials->getContent()->getLetter($essentials->getI());
        if ($end[$lastLetter] ?? false) {
            $essentials->i--;
        }
        // Restore data
        $essentials->setData($data);
        return true;
    }

    /**
     * @codeCoverageIgnore
     * Is covered by Str::getNextLetter test
     */
    public static function notParenthesis(CustomMethodEssentialsModel $essentials): bool
    {
        list($nextLetter, $nextPos) = Str::getNextLetter($essentials->getI(), $essentials->getContent());
        return $nextLetter !== "{";
    }

    public static function symbol(CustomMethodEssentialsModel $essentials, string $name = "symbol"): bool
    {
        $skipped = [
            ";" => true,
            "{" => true,
            "}" => true,
            "(" => true,
            ")" => true,
            "[" => true,
            "]" => true,
            // "/" => true,
        ];
        $res = !isset($skipped[$essentials->getLetter()])
            && (preg_match("/[\W]+/", $essentials->getLetter() ?? '') === 1)
            && !Validate::isStringLandmark($essentials->getLetter())
            && !Content::isWhitespace($essentials->getLetter());
        if ($res) {
            $essentials->appendData($essentials->getLetter(), $name);
        }
        return $res;
    }

    public static function case(CustomMethodEssentialsModel $essentials): bool
    {
        $skip = [
            "{" => "}",
            "(" => ")",
            "[" => "]",
            "//" => "\n",
            "/*" => "*/",
        ];
        $data = $essentials->getData();
        $start = $essentials->getI();
        $search = null;
        while ($essentials->getI() + 1 < $essentials->getContent()->getLength()) {
            $letter = $essentials->getContent()->getLetter($essentials->getI());
            $nextLetter = $essentials->getContent()->getLetter($essentials->getI() + 1);
            if ($skip[$letter] ?? false) {
                $key = $letter;
                $search = $skip[$letter];
            } elseif ($skip[$letter . $nextLetter] ?? false) {
                $key = $letter . $nextLetter;
                $search = $skip[$letter . $nextLetter];
            } else {
                $key = null;
                $search = null;
            }

            if ($search) {
                $essentials->i += strlen($key);
                $essentials->getMethods()['find']($essentials, $search, $key);
                $essentials->i++;
                $letter = $essentials->getContent()->getLetter($essentials->getI());
            }

            if ($essentials->getMethods()['end']($essentials) || Content::isWhitespace($letter)) {
                $case = $essentials->getContent()->iSubStr($essentials->getI() + 1, $essentials->getI() + 4);
                $caseEnd = $essentials->getContent()->getLetter($essentials->getI() + 5);
                if ($case == 'case' && (is_null($caseEnd) || Content::isWhitespace($caseEnd))) {
                    $essentials->i--;
                    break;
                }
                $break = $essentials->getContent()->iSubStr($essentials->getI() + 1, $essentials->getI() + 5);
                $breakEnd = $essentials->getContent()->getLetter($essentials->getI() + 6);
                if ($break == 'break' && (is_null($breakEnd) || $breakEnd == ';' || Content::isWhitespace($breakEnd))) {
                    $essentials->i += 6;
                    break;
                }

                $default = $essentials->getContent()->iSubStr($essentials->getI() + 1, $essentials->getI() + 7);
                $defaultEnd = $essentials->getContent()->getLetter($essentials->getI() + 8);
                if ($default == 'default' && (is_null($defaultEnd) || Content::isWhitespace($defaultEnd))) {
                    $essentials->i--;
                    break;
                }
            }
            $essentials->i++;
        }

        // Restore data
        $essentials->setData($data);
        $essentials->setLetter($essentials->getContent()->getLetter($essentials->getI()));
        return true;
    }

    /**
     * @codeCoverageIgnore
     */
    public static function decrease(CustomMethodEssentialsModel $essentials, int|string $amount = 1): void
    {
        $essentials->i -= (int) $amount;
    }

    public static function assignment(CustomMethodEssentialsModel $essentials): bool
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
        } elseif ($double[$first . $second] ?? false) {
            $move = $start + 1;
            $symbol = $first . $second;
        } elseif ($single[$first] ?? false) {
            $move = $start;
            $symbol = $first;
        } else {
            return false;
        }
        $essentials->setI($move);
        $essentials->appendData($symbol, 'assignment');
        return true;
    }

    /**
     * @codeCoverageIgnore
     */
    public static function taken(CustomMethodEssentialsModel $essentials): bool
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
    }

    /**
     * @codeCoverageIgnore
     */
    public static function strEnd(CustomMethodEssentialsModel $essentials, string $type, string $name = 'string'): bool
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
    }

    /**
     * @codeCoverageIgnore
     */
    public static function end(CustomMethodEssentialsModel $essentials): bool
    {
        $ends = [
            ";" => true,
            "/" => true,
        ];

        /** @var ?BlockModel */
        $previous = $essentials->getPrevious();
        // If previous was block
        if (!is_null($previous) && $previous->getIsBlock()) {
            $ends['}'] = true;
        }

        // If we are on symbol and there is no whitespace next
        return ($ends[$essentials->getLetter()] ?? false)
            && !Content::isWhitespace($essentials->getContent()->getLetter($essentials->getI() + 1) ?? '');
    }

    /**
     * @POSSIBLE_PERFORMANCE_ISSUE
     */
    public static function varEnd(CustomMethodEssentialsModel $essentials, bool $comma = true): bool
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
                    // list($nextLetter, $nextPos) = Str::getNextLetter($i + 1, $content);

                    if (Helper::checkIfValidVarEnd($essentials, $i + 1)) {
                        Helper::finishVarEnd($essentials, $i, $letter);
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
                || ($letter === "\n" && Helper::checkIfValidVarEnd($essentials, $i))
            ) {
                if ($letter == ',') {
                    $i--;
                }
                Helper::finishVarEnd($essentials, $i, $letter);
                return true;
            }
        }
        return true;
    }

    public static function word(CustomMethodEssentialsModel $essentials, string $name = "word", bool $varValidation = true): bool
    {
        if ($essentials->getI() >= $essentials->getContent()->getLength() || $essentials->getI() < 0) {
            return false;
        }

        list($word, $i) = Str::getNextWord($essentials->getI(), $essentials->getContent(), true);
        if (empty($word) || ($varValidation && !JsValidate::isJSValidVariable($word))) {
            return false;
        }

        $essentials->appendData($word, $name);
        $essentials->setI($i);
        return true;
    }

    /**
     * @codeCoverageIgnore
     */
    public static function isNewLine(CustomMethodEssentialsModel $essentials): bool
    {
        return $essentials->getLetter() === "\n" || $essentials->getLetter() === "\r";
    }

    // @REAL_PERFORMANCE_ISSUE but I can't figure out any other way to find end for short ifs and fors...
    public static function getNextInstruction(CustomMethodEssentialsModel $essentials): bool
    {
        $reader  = $essentials->reader;
        $start   = $essentials->getI();
        $parent  = $essentials->getParent();
        $content = $essentials->getContent()->iCutToContent($parent->getBlockStart(), $essentials->getContent()->getLength() - 1);
        $map     = $reader->getMap();
        $map[';'] = [
            "_stop" => true,
            "_custom" => [
                "semicolon" => true,
            ]
        ];
        list($resolver, $failsave) = $reader->genNewResolverAndFailsave($content, $map, $start, $parent);

        $solve = null;
        for ($i=0; $i < $content->getLength(); $i++) {
            if ($solve = $reader->resolve($resolver, $i)) {
                $reader->restoreResolver($resolver, $solve['save']);
                $resolver->setLandmark($solve['step']);
                $reader->saveBlock($resolver);
                break;
            }
        }
        if (!$solve) {
            $reader->addMissedEnd($resolver, $start, $content->getLength());
            $resolver->setI($content->getLength() - 1);
        }

        $script = $resolver->getScript();
        $last = $script[sizeof($script) - 1] ?? null;
        if ($last) {
            $essentials->setI($last->getEnd() + $parent->getBlockStart());
            if ($last->getLandmark()['semicolon'] ?? false) {
                array_splice($script, sizeof($script) - 1, 1);
                $resolver->setScript($script);
            }
        } else {
            $essentials->setI($resolver->getI() + $parent->getBlockStart());
        }
        $essentials->appendData($resolver->getScript(), 'children');

        return true;
    }

    public static function parentIsChainable(CustomMethodEssentialsModel $essentials): bool
    {
        $parent = $essentials->getParent();
        $reader = $essentials->getReader();
        if (!($parent instanceof BlockModel)) {
            return false;
        }
        $script = $reader->getCurrent()['script'];
        $previousPos = $reader->getCurrent()['caret'];
        if (is_null($script) || !$previousPos || is_null($script->getLetter($previousPos))) {
            return false;
        }
        list($startOfBlock, $pos) = Str::getPreviousLetter($previousPos, $script);
        $chainable = [
            ")" => true,
            "]" => true,
        ];
        if ($chainable[$startOfBlock] ?? false) {
            return true;
        }
        return false;
    }
}
