<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Content};
use \Tetraquark\Foundation\BlockAbstract;

class FinallyBlock extends BlockAbstract implements Contract\Block
{
    protected array $endChars = [
        '}' => true
    ];

    public function objectify(int $start = 0)
    {
        $this->setInstruction(new Content('finally {'))
            ->setInstructionStart($start - 8) // len of finally{
        ;
        list($letter, $pos) = $this->getNextLetter($start, self::$content);
        if ($letter != '{') {
            throw new Exception('Couldn\'t find start of the finally', 404);
        }
        $this->setCaret($pos);
        $this->blocks = array_merge($this->blocks, $this->createSubBlocks($pos + 1));
    }

    public function recreate(): string
    {
        $script = 'finally{';
        $blocks = '';

        foreach ($this->getBlocks() as $block) {
            $blocks .= $block->recreate();
        }

        if (\mb_strlen($blocks) > 0) {
            return $script . $blocks . '}';
        }

        return $script . '}';
    }
}
