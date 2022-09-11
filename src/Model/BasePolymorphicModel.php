<?php declare(strict_types=1);

namespace Tetraquark\Model;

use Tetraquark\{Str, Validate, Log, Exception};
use Tetraquark\Contract\BasePolymorphicModelInterface;

/**
 * Base model containing all shared functionality between polymorphic models
 */
class BasePolymorphicModel implements BasePolymorphicModelInterface
{
    protected $_methods = [];

    function __construct(array $args = [])
    {
        $this->set($args);
    }

    public function __call(string $name, array $arguments)
    {
        $method = $this->_methods[$name] ?? throw new Exception("Method " . $name . " doesn't exist", 500);
        return $method(...$arguments);
    }

    /**
     * Creates setters, getters and actual variables from passed associative array
     * @param array $args Attributes to set
     * @return self
     */
    public function set(array $args): self
    {
        $taken = ["_methods" => true];
        foreach ($args as $key => $value) {
            if (!is_string($key)) {
                throw new Exception('Array passed into BaseModel::set method should contains only associative items.', 400);
            }

            if ($taken[$key] ?? false) {
                throw new Exception('The name of variable you are trying to use (' . $key . ') is taken.', 400);
            }
            $pascalized = Str::pascalize($key);
            $camelcased = lcfirst($pascalized);
            $this->$camelcased = $value;

            $getter = 'get' . $pascalized;
            if (!isset($this->_methods[$getter])) {
                $this->_methods[$getter] = function() use ($camelcased)
                {
                    return $this->$camelcased;
                };

            }

            $setter = 'set' . $pascalized;
            if (!isset($this->_methods[$setter])) {
                $this->_methods[$setter] = function ($value) use ($camelcased)
                {
                    $this->$camelcased = $value;
                    return $this;
                };
            }

            if (is_array($value)) {
                $appender = 'append' . $pascalized;
                if (!isset($this->_methods[$appender])) {
                    $this->_methods[$appender] = function ($value, ?string $key = null) use ($camelcased)
                    {
                        if (\is_null($key)) {
                            $this->$camelcased[] = $value;
                        } else {
                            $this->$camelcased[$key] = $value;
                        }
                        return $this;
                    };
                }

                $prepender = 'prepend' . $pascalized;
                if (!isset($this->_methods[$prepender])) {
                    $this->_methods[$prepender] = function ($value) use ($camelcased)
                    {
                        array_unshift($this->$camelcased, $value);
                        return $this;
                    };
                }

                $prepender = 'merge' . $pascalized;
                if (!isset($this->_methods[$prepender])) {
                    $this->_methods[$prepender] = function ($value) use ($camelcased)
                    {
                        array_merge($this->$camelcased, $value);
                        return $this;
                    };
                }
            }
        }
        return $this;
    }

    /**
     * Returns array of available getters and setters
     * @return array
     */
    public function availableGetterAndSetters(): array
    {
        return array_keys($this->_methods);
    }

    /**
     * Transforms all data to flat array
     * @return array
     */
    public function toArray(): array
    {
        $array = [];
        foreach ($this->_methods as $methodName => $method) {
            if ($methodName[0] . $methodName[1] . $methodName[2] === 'get') {
                $array[lcfirst(substr($methodName, 3))] = $method();
            }
        }
        return $array;
    }
}
