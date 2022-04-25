<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log as Log, Exception as Exception, Contract as Contract, Validate as Validate};
use \Tetraquark\Abstract\MethodBlockAbstract as MethodBlock;

class NewClassBlock extends MethodBlock implements Contract\Block
{
    protected string $className = '';

    public function objectify(int $start = 0)
    {
        $this->setName('');
        $searchForClosing = false;
        $nameStarted      = false;
        $nameEnded        = false;
        $end              = null;

        for ($i=$start; $i < \mb_strlen(self::$content); $i++) {
            $letter = self::$content[$i];
            if (Validate::isWhitespace($letter)) {
                if ($nameStarted) {
                    $nameEnded = true;
                }
                continue;
            } elseif (!$searchForClosing && $letter == '(') {
                $this->setClassName(\mb_substr(self::$content, $start, $i - $start));
                $searchForClosing = true;
                continue;
            } elseif ($letter == ';') {
                $end = $i;
                $this->setClassName(\mb_substr(self::$content, $start, $end - $start));
                break;
            } elseif (!$searchForClosing) {
                // If its non whitespace char and we don't search for ')'
                if ($nameEnded) {
                    $end = $i - 1;
                    $this->setClassName(\mb_substr(self::$content, $start, $end - $start));
                    break;
                }
                $nameStarted = true;
            } elseif ($searchForClosing && $letter == ')') {
                $end = $i + 1;
                break;
            }
        }

        if (\is_null($end)) {
            $end = $i;
            $this->setClassName(\mb_substr(self::$content, $start, $end - $start));
        }

        $this->setCaret($end);

        $instruction = \mb_substr(self::$content, $start, $end - $start);

        $this->setInstruction($instruction)
            ->setInstructionStart($start - \mb_strlen('new '))
        ;
        $this->findAndSetArguments();
    }

    public function recreate(): string
    {
        $script = 'new ' . $this->getAlias($this->getClassName());
        $args = $this->getArguments();
        if (\sizeof($args) == 0) {
            return $script . ';';
        }
        $script .= '(';
        foreach ($args as $arg) {
            foreach ($arg as $block) {
                $script .= rtrim($block->recreate(), ';');
            }
            $script .= ',';
        }
        $script = rtrim($script, ',');
        return $script . ');';
    }

    protected function setClassName(string $name): void
    {
        $this->className = trim($name);
    }

    protected function getClassName(): string
    {
        return $this->className;
    }
}
