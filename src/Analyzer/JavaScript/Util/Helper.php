<?php declare(strict_types=1);

namespace Tetraquark\Analyzer\JavaScript\Util;

use Orator\Log;
use Tetraquark\{Validate, Str};
use Tetraquark\Analyzer\JavaScript\Validate as JSValidate;
use Content\Utf8 as Content;
use Tetraquark\Model\CustomMethodEssentialsModel;

abstract class Helper
{
    public static function checkIfValidVarEnd(CustomMethodEssentialsModel $essentials, int $i): bool
    {
        $content = $essentials->getContent();

        list($nextLetter, $nextPos) = Str::getNextLetter($i, $content);

        if (strlen($nextLetter) == 0) {
            // End of file
            return true;
        }

        // If its empty from the begining and current letter is not an semicolon then it's not the end
        if (Content::isWhitespace($content->iSubStr($essentials->getI(), $nextPos - 1)) && $nextLetter != ';') {
            return false;
        }

        if (
            JsValidate::isOperator($nextLetter, true)
            && !Validate::isStringLandmark($nextLetter, '')
            && !JsValidate::isComment($nextPos, $content)
        ) {
            return false;
        }

        list($prevLetter, $prevPos) = Str::getPreviousLetter($i, $essentials->getContent());
        if (
            JsValidate::isOperator($prevLetter)
            && !Validate::isStringLandmark($prevLetter, '')
            && !JsValidate::isComment($prevPos, $content)
        ) {
            return false;
        }

        list($previousWord) = Str::getPreviousWord($i, $content);
        if (JsValidate::isExtendingKeyWord($previousWord)) {
            return false;
        }

        list($nextWord) = Str::getNextWord($i, $content);
        if (JsValidate::isExtendingKeyWord($nextWord)) {
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
        if ($pos >= $content->getLength()) {
            return $pos - 1;
        }

        list($letter, $newPos) = Str::getNextLetter($pos, $content);

        if ($letter == '.') {
            list($nextWord, $wordPos) = Str::getNextWord($newPos + 1, $content, !Content::isWhitespace($content->getLetter($newPos + 1)));
            return self::getNextChain($essentials, $wordPos + 1);
        } elseif ($letter == "=") {
            $data = $essentials->getData();
            $essentials->getMethods()['varend']($essentials);
            $essentials->setData($data);
            return $essentials->getI();
        } elseif ($letter == "(" || $letter == "[") {
            $data = $essentials->getData();
            $essentials->setI($newPos + 1);
            if ($letter == "(") {
                $essentials->getMethods()['find']($essentials, ")", "(", "find");
            } else {
                $essentials->getMethods()['find']($essentials, "]", "[", "find");
            }
            $essentials->setData($data);
            return self::getNextChain($essentials, $essentials->getI() + 1);
        } elseif ($letter == '?') {
            list($letter, $dotPos) = Str::getNextLetter($newPos + 1, $content);
            if ($letter == '.') {
                list($nextWord, $wordPos) = Str::getNextWord($dotPos + 1, $content, !Content::isWhitespace($content->getLetter($dotPos + 1)));
                return self::getNextChain($essentials, $wordPos + 1);
            }
        }

        return $pos;
    }
}
