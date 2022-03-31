<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\Log as Log;
use \Tetraquark\Contract as Contract;
use \Tetraquark\MethodBlock as MethodBlock;

class InstanceMethod extends MethodBlock implements Contract\Block
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
        $this->findInstructionEnd($start, '', $this->instructionEnds);
        $this->findAndSetName('', ['(' => true]);
        $this->createSubBlocks();
        $this->setSubtype('method');
        $this->findAndSetArguments();
    }

    public function recreate(): string
    {
        $script = $this->getAlias($this->getName()) . '(' . $this->getAliasedArguments() . '){';
        foreach ($this->getBlocks() as $block) {
            $script .= $block->recreate();
        }
        return $script . '}';
    }
}
