<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Content};
use \Tetraquark\Foundation\BlockAbstract;

class TryBlock extends BlockAbstract implements Contract\Block
{
    protected array $endChars = [
        '}' => true
    ];
    protected array $catchAr = [];

    public function objectify(int $start = 0)
    {
        $this->setInstruction(new Content('try {'))
            ->setInstructionStart($start - 4) // 4 = len of try{
        ;
        list($letter, $pos) = $this->getNextLetter($start, self::$content);
        if ($letter != '{') {
            throw new Exception('Couldn\'t find start of the try', 404);
        }
        $this->setCaret($pos);
        $this->blocks = array_merge($this->blocks, $this->createSubBlocks($pos + 1));
        list($word, $pos) = $this->getNextWord($this->getCaret(), self::$content);
        if (\mb_substr($word, 0, 5) === 'catch') {
            // pos: minus the whole word plus 6 to place caret just after `catch` keyword
            $this->setCatch(new CatchBlock($pos - \mb_strlen($word) + 6, 'catch', $this));
        }
    }

    protected function setCatch(CatchBlock $catch): self
    {
        $catch->setPlacement('getCatch');
        $catch->setChildIndex(\sizeof($this->catchAr));
        $this->setCaret($catch->getCaret());
        $this->catchAr[] = $catch;
        return $this;
    }

    public function getCatch(): array
    {
        return $this->catchAr;
    }

    public function recreate(): string
    {
        $script = 'try {';
        $blocks = '';

        foreach ($this->getBlocks() as $block) {
            $blocks .= $block->recreate();
        }

        if (\mb_strlen($blocks) > 0) {
            $script .= rtrim($blocks, ';') . ';}';
        } else {
            $script .= '}';
        }


        if (\sizeof($this->catchAr) > 0) {
            foreach ($this->catchAr as $catch) {
                $script .= $catch->recreate();
            }
        }

        return $script;
    }
}
