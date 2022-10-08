<?php declare(strict_types=1);

namespace Tetraquark\Model;

use Tetraquark\{Str, Validate, Exception};
use Tetraquark\Contract\BaseModelInterface;

/**
 * Base model containing all shared functionality between models
 */
abstract class BaseModel implements BaseModelInterface, \JsonSerializable
{
    public function toArray(): array
    {
        $vars = get_class_vars(get_class($this));
        $array = [];
        foreach ($vars as $key => $value) {
            $array[$key] = $this->$key;
        }
        return $array;
    }

    public function jsonSerialize(): mixed
    {
        $res = $this->toArray();
        foreach ($res as $key => $value) {
            if ($value instanceof BaseModelInterface) {
                $res[$key] = $value::class;
            }
        }
        return $res;
    }
}
