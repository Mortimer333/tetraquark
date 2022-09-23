<?php declare(strict_types=1);

namespace Tetraquark\Factory;

use Tetraquark\{Str, Validate, Log, Exception};
use Tetraquark\Model\CustomMethodEssentialsModel;

class ClosureFactory extends BaseFactory
{

    public static function generateReversalClosure(string $letter): \Closure
    {
        return function (CustomMethodEssentialsModel $essentials) use ($letter): bool
        {
            $res = $letter != $essentials->getLetter();
            if ($res) {
                $essentials->setData([...$essentials->getData(), "negation" => $essentials->getLetter()]);
            }
            return $res;
        };
    }

    public static function generateEqualClosure(string $letter): \Closure
    {
        return function (CustomMethodEssentialsModel $essentials) use ($letter): bool
        {
            return $letter == $essentials->getLetter();
        };
    }
}
