<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Content};
use \Tetraquark\Foundation\BlockAbstract as Block;

class OperatorBlock extends Block implements Contract\Block
{
    protected bool $addSemicolon = false;

    public function objectify(int $start = 0)
    {
        if ($this->getSubtype() === '--') {
            $this->setInstruction(new Content('--'));
        } else {
            $this->setInstruction(new Content('++'));
        }
        $this->setName('');
        $this->setCaret($start + 1);
        $this->setInstructionStart($start - 1);
        for ($i=$start; $i >= 0; $i--) {
            $letter = self::$content->getLetter($i);
            if ($letter == ' ') {
                continue;
            }

            if ($letter == "\n" || $letter == ";") {
                $this->addSemicolon = false;
                break;
            }

            $this->addSemicolon = true;
            break;
        }
    }

    public function recreate(): string
    {
        $script = $this->getInstruction()->__toString();
        if ($this->addSemicolon) {
            $script .= ';';
        }
        return $script;
    }
}
