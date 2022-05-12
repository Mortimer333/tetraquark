<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate};
use \Tetraquark\Foundation\MethodBlockAbstract as MethodBlock;

class ClassMethodBlock extends MethodBlock implements Contract\Block
{
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
    }

    public function recreate(): string
    {
        $script = $this->getAlias($this->getName()) . '(' . $this->getAliasedArguments() . '){';
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
