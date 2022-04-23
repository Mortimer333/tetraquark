<?php declare(strict_types=1);

namespace Tetraquark\Abstract;
use \Tetraquark\{Exception as Exception, Block as Block, Log as Log};

abstract class VariableBlockAbstract extends BlockAbstract
{
    public function recreate(): string
    {
        $script = $this->getSubType() . ' ' . $this->getAlias($this->getName());

        if (\sizeof($this->getBlocks()) > 0 || \mb_strlen($this->getValue()) > 0) {
            $script .= '=';
        }

        foreach ($this->getBlocks() as $block) {
            $script .= $block->recreate();
        }

        $value = $this->removeAdditionalSpaces($this->getValue());
        if (\mb_strlen($value) > 0) {
            $script .= $this->replaceVariablesWithAliases($value);
        }
        $scriptLastLetter = $script[\mb_strlen($script) - 1];
        $addSemiColon = [
            ';' => false,
            ',' => false
        ];

        if ($addSemiColon[$scriptLastLetter] ?? true) {
            $script .= ';';
        }
        return $this->removeAdditionalSpaces($script);
    }
}
