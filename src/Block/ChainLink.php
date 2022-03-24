<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\Log as Log;
use \Tetraquark\Contract as Contract;
use \Tetraquark\Block as Block;

class ChainLink extends Block implements Contract\Block
{
    protected const FIRST = 'first';
    public function __construct(
        int $start,
        protected string $subtype = '',
        protected array  $data  = []
    ) {
        parent::__construct($start, $subtype, $data);
    }
    public function objectify(int $start = 0)
    {
        if ($this->getSubtype() == self::FIRST) {
            $this->findInstructionStart($start, [' ' => true, "\n" => true, "\r" => true, '.' => true, ';' => true]);
        } else {
            /** @TODO We have to include cases like this:
             * run.this.man
             * ();
             * or
             * or.run.this
             *  = 'aa';
             * or
             * const var = do.get.
             * one;
             * so we have to ignore new line and try to go to next lines until we hit `(`/`=`/`.` and do the rest or if we anythign else than
             * those symbols and whitespace then break check.
             */
            // $this->findInstructionEnd($start, '', ["\n" => true, "\r" => true, '.' => true, ';' => true, '(' => true, '=' => true]);
            // $instruction = $this->getInstruction();
            // $lastLetter = $instruction[\mb_strlen($instruction) - 1];
            // if ($lastLetter == '(' || $lastLetter == '.') {
            //     // Minus 2 because caret is on the next letter
            //     $this->setCaret($this->getCaret() - 2);
            //     $instruction = \mb_substr($instruction, 0, -1);
            //     $instrLen = \mb_strlen($instruction);
            //     $this->setInstructionStart($this->getCaret() - $instrLen)
            //         ->setInstructionLength($instrLen)
            //         ->setInstruction($instruction);
            // }
        }

        $this->setName('');
    }

    public function recreate(): string
    {
        $script = $this->replaceVariablesWithAliases($this->getInstruction());

        foreach ($this->getBlocks() as $block) {
            $script .= $block->recreate();
        }

        return $script;
    }
}
