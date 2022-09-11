<?php declare(strict_types=1);

namespace Tetraquark\Model;

use Tetraquark\{Str, Validate, Log, Exception};
use Tetraquark\Contract\BaseModelInterface;

/**
 * Base model containing all shared functionality between models
 */
abstract class BaseModel implements BaseModelInterface
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
}
