<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\Log as Log;
use \Tetraquark\Contract as Contract;
use \Tetraquark\Block as Block;

class ObjectValueBlock extends Block implements Contract\Block
{
    protected array $endChars = [
        "," => true,
        "}" => true,
    ];

    public function objectify(int $start = 0)
    {
        $this->findInstructionStart($start - 1, [
            "," => true,
            "{" => true,
        ]);
        $this->setName(
            $this->removeStringCharsIfPossible(
                trim($this->getInstruction())
            )
        );
        $this->blocks = array_merge($this->blocks, $this->createSubBlocks($start + 1));
        $lastLetter = self::$content[$this->getCaret()];
        if ($lastLetter == '}') {
            $this->setCaret($this->getCaret() - 1);
        }
    }

    protected function removeStringCharsIfPossible(string $name): string
    {
        for ($i=1; $i < \mb_strlen($name) - 1; $i++) {
            $letter = $name[$i];
            if ($this->isWhitespace($letter) || $this->isSpecial($letter) || $this->isString($letter)) {
                return $name;
            }
        }
        if ($this->isString($name[0])) {
            return trim($name, $name[0]);
        }
        return $name;
    }

    public function recreate(): string
    {
        $script = $this->replaceVariablesWithAliases($this->getName()) . ":";

        foreach ($this->getBlocks() as $block) {
            $script .= rtrim($block->recreate(), ';');
        }

        return trim($script) . ",";
    }
}
