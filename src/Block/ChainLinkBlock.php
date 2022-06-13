<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Content};
use \Tetraquark\Foundation\BlockAbstract as Block;

class ChainLinkBlock extends Block implements Contract\Block
{
    public const FIRST = 'chain:first';
    public const BRACKET = 'chain:bracket';
    public const MIDDLE_BRACKET = 'chain:middle:bracket';
    public const END_METHOD = 'chain:end:method';
    public const END_VARIABLE = 'chain:end:variable';
    public const BRACKET_BLOCK_CREATE = 'chain:bracket:create';
    protected Contract\Block $methodValues;
    protected string $firstLink;
    protected array $bracketBlocks = [];
    protected bool $isBracket = false;

    public function objectify(int $start = 0)
    {
        /*
            when encounter dot:
            - check if first by checking if parent is ChainLinkBlock
                - if first then find instruction start with findInstructionStart
                - add found link to the special variable `first`
                - procced normally with the rest of the method
            - check the current letter
                - if it's dot then find where it ends with findInstructionEnd (it will end on all normal (';', "\n") and special symbols)
                - if it's bracket '[' then find its end `]` and create subblocks from whats between then proceed
            - find next closest letter
                - if it's dot or bracket, create new ChinLinkBlock child
                - if it's = create new AttributeBlock child
                - if it's ( create new CallerBlock and then repeat this check
            - end it
            first.middle.middle['middle 2'].var = first.method()
         */
        $this->setName('');
        $parent = $this->getParent();
        $endChars = array_merge([';' => true], Validate::getSpecial());
        $realStart = null;

        $mapTrigger = self::$content->getLetter($start);
        if (!($parent instanceof ChainLinkBlock) || $parent->getMode() === self::BRACKET_BLOCK_CREATE) {
            $this->findInstructionStart($start, $endChars);
            $realStart = $this->getInstructionStart();
            $this->firstLink = $this->getInstruction()->__toString();
            $this->setSubtype(self::FIRST);
        }

        if ($mapTrigger === '.') {
            $this->findInstructionEnd($start + 1, '', $endChars);
        } elseif ($mapTrigger === '[') {
            if ($this->getSubtype() === self::FIRST) {
                $oldStart = $this->getInstructionStart();
            }
            $this->findInstructionEnd($start + 1, '', [']' => true]);
            if (isset($oldStart)) {
                $this->setInstructionStart($oldStart);
            }
            $bracketContent = $this->getInstruction()->__toString();
            $this->setInstruction(new Content(''));
            $this->setMode(self::BRACKET_BLOCK_CREATE);
            $this->bracketBlocks = $this->createSubBlocksWithContent($bracketContent);
            foreach ($this->bracketBlocks as &$block) {
                $block->setPlacement('getBracketBlocks');
            }
            $this->setMode(self::DEFAULT_MODE);
            $this->isBracket = true;
            $this->setCaret($this->getCaret() + 1);
        }

        if ($this->getSubtype() === self::FIRST) {
            $this->setInstructionStart($realStart);
            if ($this->isBracket) {
                $this->setInstruction(new Content($this->firstLink));
            } else {
                $this->setInstruction(new Content($this->firstLink . '.' . $this->getInstruction()));
            }
        }

        $this->resolvePossibleTrigger();
    }

    public function getBracketBlocks(): array
    {
        return $this->bracketBlocks;
    }

    protected function resolvePossibleTrigger(): void
    {
        list($trigger, $pos) = $this->getNextLetter($this->getCaret(), self::$content);
        $switch = [
            '.' => function (int $pos) {
                $chain = new ChainLinkBlock($pos, '.', $this);
                $chain->setChildIndex(\sizeof($this->getBlocks()));
                $this->addBlock($chain);
                $this->setCaret($chain->getCaret());
            },
            '[' => function (int $pos) {
                $chain = new ChainLinkBlock($pos, '[', $this);
                $chain->setChildIndex(\sizeof($this->getBlocks()));
                $this->addBlock($chain);
                $this->setCaret($chain->getCaret());
            },
            '(' => function (int $pos) {
                $caller = new CallerBlock($pos, '(', $this);
                $caller->setChildIndex(\sizeof($this->getBlocks()));
                $this->addBlock($caller);
                list($letter, $pos) = $this->getNextLetter($caller->getCaret() + 1, self::$content);
                $this->setCaret($pos);
                $this->resolvePossibleTrigger();
            },
            '=' => function (int $pos) {
                $attribute = new AttributeBlock($pos, '=', $this);
                $attribute->setName('');
                $attribute->setChildIndex(\sizeof($this->getBlocks()));
                $this->addBlock($attribute);
                $this->setCaret($attribute->getCaret());
            },
            'default' => function (int $pos) {}
        ];
        ($switch[$trigger] ?? $switch['default'])($pos);
    }

    public function recreate(): string
    {
        $subtype = $this->getSubtype();
        $script  = '';


        if ($subtype !== self::FIRST && !$this->isBracket) {
            $script .= '.';
        }
        Log::log('==========');

        $script .= $this->getInstruction();
        Log::log($script);
        if ($this->isBracket) {
            $script .= "[";
            foreach ($this->getBracketBlocks() as $block) {
                $script .= rtrim($block->recreate(), ';');
            }
            $script .= "]";
        }
        Log::log($script);

        foreach ($this->getBlocks() as $block) {
            $script .= rtrim($block->recreate(), ';');
        }

        Log::log($script);
        if (!($this->getParent() instanceof ChainLinkBlock)) {
            return $script . ';';
        }

        return $script;
    }

    public function getMethodValues(): ?Contract\Block
    {
        return $this->methodValues ?? null;
    }

    private function getLastLink(?Contract\Block $block = null): Contract\Block
    {
        if (is_null($block)) {
            $block = $this;
        }
        $link = $block->getBlocks()[\sizeof($block->getBlocks()) - 1] ?? null;
        if (\is_null($link)) {
            return $block;
        }
        return $this->getLastLink($link);
    }
}
