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
        $searchForWhitespace = true;
        for ($i=$start; $i >= 0; $i--) {
            $letter = self::$content[$i];
            if ($searchForWhitespace && $this->isWhitespace($letter)) {
                $start = $i + 1;
                break;
            }
            if (!$this->isWhitespace($letter)) {
                $searchForWhitespace = true;
            }
        }
        $this->findInstructionEnd($start, '', $this->instructionEnds);
        $this->findAndSetName('', ['(' => true]);
        $this->createSubBlocks();
        $this->setSubtype('method');
        $this->findAndSetArguments();
    }

    protected function findAndSetArguments(): void
    {
        $instr = $this->getInstruction();
        $startSettingArgs = false;
        $word = '';
        for ($i=\strlen($instr) - 1; $i >= 0; $i--) {
            $letter = $instr[$i];
            if (!$startSettingArgs && $letter == ')') {
                $startSettingArgs = true;
                continue;
            }

            if ($startSettingArgs && $this->isWhitespace($letter)) {
                continue;
            }

            if ($startSettingArgs && $letter == '(') {
                $this->addArgument(strrev($word));
                break;
            }

            if ($startSettingArgs && $letter == ',') {
                $this->addArgument(strrev($word));
                $word = '';
                continue;
            }

            if ($startSettingArgs) {
                $word .= $letter;
            }
        }

        $this->arguments = array_reverse($this->arguments);
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
