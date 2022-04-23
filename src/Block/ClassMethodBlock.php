<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\Log as Log;
use \Tetraquark\Contract as Contract;
use \Tetraquark\Abstract\MethodBlockAbstract as MethodBlock;

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
        Log::increaseIndent();
        // Search for the name of the function
        for ($i=$start - 1; $i >= 0; $i--) {
            $letter = self::$content[$i];
            if ($searchForWhitespace && $this->isWhitespace($letter)) {
                $start = $i + 1;
                break;
            }
            if (!$this->isWhitespace($letter)) {
                $searchForWhitespace = true;
            }
        }
        Log::decreaseIndent();
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
