<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate};
use \Tetraquark\Foundation\MethodBlockAbstract as MethodBlock;

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

        for ($i=$start; $i < self::$content->getLength(); $i++) {
            $letter = self::$content->getLetter($i);
            if (
                ($startsTemplate = Validate::isTemplateLiteralLandmark($letter, ''))
                || Validate::isStringLandmark($letter, '')
            ) {
                $i = $this->skipString($letter, $i + 1, self::$content, $startsTemplate);
                $letter = self::$content->getLetter($i);
            }

            if (Validate::isWhitespace($letter)) {
                if ($nameStarted) {
                    $nameEnded = true;
                }
                continue;
            } elseif (!$searchForClosing && $letter == '(') {
                $this->setClassName(self::$content->iSubStr($start, $i - 1));
                $searchForClosing = true;
                continue;
            } elseif ($letter == ';') {
                $end = $i;
                $this->setClassName(self::$content->iSubStr($start, $end - 1));
                break;
            } elseif (!$searchForClosing) {
                // If its non whitespace char and we don't search for ')'
                if ($nameEnded) {
                    $end = $i - 1;
                    $this->setClassName(self::$content->iSubStr($start, $end - 1));
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
            $this->setClassName(self::$content->iSubStr($start, $end - 1));
        }

        $this->setCaret($end);

        $this->setInstruction(self::$content->iCutToContent($start, $end - 1))
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
