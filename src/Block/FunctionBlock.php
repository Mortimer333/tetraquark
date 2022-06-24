<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Content};
use \Tetraquark\Foundation\MethodBlockAbstract as MethodBlock;

class FunctionBlock extends MethodBlock implements Contract\Block
{
    public const ANONYMOUS = 'anonymous:function';
    protected array $endChars = [
        '}' => true
    ];

    protected array $instructionEnds = [
        '{' => true,
    ];

    protected bool $generator = false;

    public function objectify(int $start = 0)
    {
        $endLandmark = self::$content->getLetter($start);
        if ($endLandmark == '*') {
            $this->setGenerator(true);
        }
        $this->findMethodEnd($start - 8);
        $this->findAndSetName('function ', ['(' => true]);
        $this->blocks = array_merge($this->blocks, $this->createSubBlocks());
        if (\mb_strlen($this->getName()) == 0) {
            $this->setSubtype(self::ANONYMOUS);
        }
        $this->findAndSetArguments();
        $this->checkForPrefixes();
    }

    protected function setGenerator(bool $isGenerator): self
    {
        $this->generator = $isGenerator;
        return $this;
    }

    public function isGenerator(): bool
    {
        return $this->generator;
    }

    public function recreate(): string
    {
        $script = $this->recreatePrefix() . 'function';
        if ($this->generator) {
            $script .= '*';
        }
        $script .= ' ' . $this->getAlias($this->getName()) . '(' . $this->getAliasedArguments() . '){';
        $blocks = '';

        foreach ($this->getBlocks() as $block) {
            $blocks .= $block->recreate();
        }

        if (\mb_strlen($blocks) > 0) {
            return $script . rtrim($blocks, ';') . ';}';
        }

        return $script . '}';
    }

    public function recreateForImport(): string
    {
        $script = $this->getAlias($this->getName()) . ' = ' . $this->recreatePrefix() . 'function';
        if ($this->generator) {
            $script .= '*';
        }
        $script .= '(' . $this->getAliasedArguments() . '){';
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
