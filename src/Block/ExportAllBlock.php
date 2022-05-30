<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Content};
use \Tetraquark\Foundation\BlockAbstract as Block;

class ExportAllBlock extends Block implements Contract\Block
{
    protected string $newName = '';
    public const NEW_NAME = 'new name';

    public function objectify(int $start = 0)
    {
        list($letter, $pos) = $this->getNextLetter($start + 1, self::$content);
        $this->setInstructionStart($start - 1);
        if ($letter . self::$content->getLetter($pos + 1) == 'as') {
            list($nextWord, $pos) = $this->getNextWord($pos + 1, self::$content);
            $this->setNewName($nextWord);
            $this->setInstruction(new Content('* as ' . $nextWord))
                ->setCaret($pos)
                ->setSubtype(self::NEW_NAME)
            ;
        } else {
            $this->setInstruction(new Content('*'))
                ->setCaret($start)
            ;
        }
    }

    public function recreate(): string
    {
        $script = "*";
        if ($this->getSubtype() === self::NEW_NAME) {
            $script .= "as " . $this->getNewName();
        }
        return $script;
    }

    public function getNewName(): string
    {
        return $this->newName;
    }

    protected function setNewName(string $newName): self
    {
        $this->newName = $newName;
        return $this;
    }
}
