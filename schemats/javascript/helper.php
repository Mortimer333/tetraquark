<?php

require_once __DIR__ . '/validate.php';

use Tetraquark\Validate;

class Helper
{
    public static function checkIfValidVarEnd(CustomMethodEssentialsModel $essentials, int $i): bool
    {
        $content = $essentials->getContent();
        list($prevLetter, $prevPos) = Str::getPreviousLetter($i, $essentials->getContent());
        if (
            Validate::isOperator($prevLetter)
            && !Validate::isStringLandmark($prevLetter, '')
            && !JsValidate::isComment($prevPos, $content)
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
            && !JsValidate::isComment($nextPos, $content)
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
        list($letter, $newPos) = Str::getNextLetter($pos, $content);

        if ($letter == '.') {
            list($nextWord, $wordPos) = Str::getNextWord($newPos + 1, $content, !Validate::isWhitespace($content->getLetter($newPos + 1)));
            return self::getNextChain($essentials, $wordPos + 1);
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
            return self::getNextChain($essentials, $essentials->getI() + 1);
        }

        return $pos;
    }
}
