<?php declare(strict_types=1);

namespace Tetraquark\Analyzer\JavaScript;

use Orator\Log;
use Content\Utf8 as Content;
use Tetraquark\{Str, Validate};
use Tetraquark\Analyzer\JavaScript\Validate as JsValidate;
use Tetraquark\Analyzer\JavaScript\Util\Helper;
use Tetraquark\Model\CustomMethodEssentialsModel;

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
            "read" => "read",
            "case" => "case",
            "taken" => "taken",
            "number" => "number",
            "symbol" => "symbol",
            "strend" => "strEnd",
            "varend" => "varEnd",
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

    public static function number(CustomMethodEssentialsModel $essentials): bool
    {
        $content = $essentials->getContent();
        if (is_numeric($essentials->getLetter())) {
            $end = $content->getLength() - 1;
            for ($i=$essentials->getI() + 1; $i < $content->getLength(); $i++) {
                $letter = $content->getLetter($i);
                if (!is_numeric($letter) && $letter != '.') {
                    $end = $i - 1;
                    break;
                }
            }
            // If number is constructed like this `2.` then it's not a number
            if ($content->getLetter($end) === '.') {
                return false;
            }
            $essentials->appendData($content->iSubStr($essentials->getI(), $end), "number");
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

    /**
     * @codeCoverageIgnore
     * Is covered by ReaderGenericTest
     */
    public static function read(CustomMethodEssentialsModel $essentials, string $valueName, ?string $name = null): void
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

    /**
     * @codeCoverageIgnore
     */
    public static function case(CustomMethodEssentialsModel $essentials): bool
    {
        $possibleCase = $essentials->getContent()->subStr($essentials->getI() + 1, 4);
        if ($possibleCase === 'case') {
            return true;
        }
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public static function decrease(CustomMethodEssentialsModel $essentials, int $amount = 1): void
    {
        $essentials->i -= $amount;
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

        return $ends[$essentials->getLetter()] ?? false;
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
                    list($nextLetter, $nextPos) = Str::getNextLetter($i + 1, $content);

                    if (!JsValidate::isOperator($nextLetter)) {
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
}
