<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Content};
use \Tetraquark\Foundation\BlockAbstract as Block;

class ExportFromBlock extends Block implements Contract\Block, Contract\ExportBlock
{
    protected string $path;

    public function objectify(int $start = 0)
    {
        $startLetter = self::$content->getLetter($start);
        $this->setInstructionStart($start - \mb_strlen('from '));
        $pathStart   = $start;
        if (Validate::isWhitespace($startLetter)) {
            list($startLetter, $pathStart) = $this->getNextLetter($pathStart, self::$content);
        }

        if (!Validate::isStringLandmark($startLetter)) {
            throw new Exception("From Block doesn't have properly set path", 400);
        }

        $end = $this->skipString($startLetter, $pathStart + 1, self::$content, $startLetter === '`');
        $this->setInstruction(self::$content->iCutToContent($this->getInstructionStart(), $end - 1))
            ->setCaret($end)
            ->setPath(self::$content->iSubStr($pathStart + 1, $end - 2))
        ;
    }

    protected function setPath(string $path): self
    {
        $this->path = $path;
        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function recreate(): string
    {
        return " from '" . $this->getPath() . "';";
    }
}
