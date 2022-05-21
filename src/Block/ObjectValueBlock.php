<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Content};
use \Tetraquark\Foundation\BlockAbstract as Block;

class ObjectValueBlock extends Block implements Contract\Block
{
    protected array $endChars = [
        "," => true,
        "}" => true,
    ];

    public function objectify(int $start = 0)
    {
        list($previousLetter) = $this->getPreviousLetter($start - 1, self::$content);
        if ($previousLetter !== ']') {
            $this->findInstructionStart($start, [
                "," => true,
                "{" => true,
            ]);

            $this->setName(
                $this->removeStringCharsIfPossible(
                    $this->getInstruction()->trim()
                )
            );
        } else {
            $this->setName('')
                ->setInstruction(new Content(''))
                ->setInstructionStart($start)
                ->setCaret($start);
        }


        $this->blocks = array_merge($this->blocks, $this->createSubBlocks($start + 1));
        $lastLetter = self::$content->getLetter($this->getCaret());
        $nextLastLetter = self::$content->getLetter($this->getCaret() + 1) ?? '';
        if ($lastLetter == '}') {
            $this->setCaret($this->getCaret() - 1);
        }
    }

    protected function removeStringCharsIfPossible(Content $name): string
    {
        for ($i=1; $i < $name->getLength() - 1; $i++) {
            $letter = $name->getLetter($i);
            if (Validate::isWhitespace($letter) || Validate::isSpecial($letter) || Validate::isStringChar($letter)) {
                return $name->__toString();
            }
        }

        if (Validate::isStringChar($name->getLetter(0) ?? '')) {
            return trim($name->__toString(), $name->getLetter(0));
        }
        return $name->__toString();
    }

    public function recreate(): string
    {
        $script = $this->replaceVariablesWithAliases(new Content($this->getName())) . ":";

        foreach ($this->getBlocks() as $block) {
            $script .= rtrim($block->recreate(), ';');
        }

        return trim($script) . ",";
    }
}
