<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Content};
use \Tetraquark\Foundation\MethodBlockAbstract as MethodBlock;

class ClassMethodBlock extends MethodBlock implements Contract\Block
{
    public const STATIC_METH = 'class:method:static';
    protected array $endChars = [
        '}' => true
    ];

    protected array $instructionEnds = [
        '{' => true,
    ];

    public function objectify(int $start = 0)
    {
        $searchForWhitespace = false;
        // Search for the name of the function
        for ($i=$start - 1; $i >= 0; $i--) {
            $letter = self::$content->getLetter($i);
            if ($searchForWhitespace && Validate::isWhitespace($letter)) {
                $start = $i + 1;
                break;
            }
            if (!Validate::isWhitespace($letter)) {
                $searchForWhitespace = true;
            }
        }
        $this->findMethodEnd($start);
        $this->findAndSetName('', ['(' => true]);
        $this->blocks = array_merge($this->blocks, $this->createSubBlocks());
        $this->setSubtype('method');
        $this->findAndSetArguments();


        if ($this->getParent() instanceof ClassBlock) {
            list($word, $pos) = $this->getPreviousWord($this->getInstructionStart() - 1, self::$content);
            if (\mb_substr($word, -6) === 'static') {
                $this->setInstructionStart($pos - 6)
                    ->setInstruction(new Content('static ' . $this->getInstruction()))
                    ->setSubtype(self::STATIC_METH)
                ;
            }
        }
    }

    public function recreate(): string
    {
        $script = '';
        if ($this->getSubtype() === self::STATIC_METH) {
            $script .= 'static ';
        }
        $script .= $this->getName() . '(' . $this->getAliasedArguments() . '){';
        $blocks = '';
        foreach ($this->getBlocks() as $block) {
            $blocks .= $block->recreate();
        }
        if (\mb_strlen($blocks) > 0) {
            return $script . rtrim($blocks, ';') . ';}';
        }
        return $script . '}';
    }
}
