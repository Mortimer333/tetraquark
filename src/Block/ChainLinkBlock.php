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
    private array $triggerSwitch;

    public function objectify(int $start = 0)
    {
        $this->setTriggerSwitch();
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
            list($letter, $pos) = $this->getNextLetter($start + 1, self::$content);
            $this->findInstructionEnd($pos, '', $endChars);
            $endLetter = self::$content->getLetter($this->getCaret());
            $this->setCaret($this->getCaret() - 1);
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

    private function setTriggerSwitch(): void
    {
        $this->triggerSwitch = [
            '.' => function (int $pos) {
                $this->addChainToBlocks($pos, '.');
            },
            '[' => function (int $pos) {
                $this->addChainToBlocks($pos, '[');
            },
            '(' => function (int $pos) {
                $caller = new CallerBlock($pos, '(', $this);
                $caller->setChildIndex(\sizeof($this->getBlocks()));
                $this->addBlock($caller);
                $blockSize = sizeof($this->getBlocks());
                $this->setCaret($caller->getCaret());
                $this->resolvePossibleTrigger();
            },
            '=' => function (int $pos) {
                $this->addAttributeToBlocks($pos);
            },
            '-' => function (int $pos) {
                $this->addOperatorToBlocks($pos, '-');
            },
            '+' => function (int $pos) {
                $this->addOperatorToBlocks($pos, '+');
            },
            'default' => false
        ];
    }

    private function addOperatorToBlocks(int $pos, string $type): void
    {
        $nextLetter = self::$content->getLetter($pos + 1);
        if ($nextLetter == "=") {
            return;
        } elseif ($nextLetter != $type) {
            return;
        }

        $operator = new OperatorBlock($pos, $type . $type, $this);
        $operator->setChildIndex(\sizeof($this->getBlocks()));
        $this->addBlock($operator);
        $this->setCaret($operator->getCaret());
    }

    private function addAttributeToBlocks(int $pos): void
    {
        $attribute = new AttributeBlock($pos, '=', $this);
        $attribute->setChildIndex(\sizeof($this->getBlocks()));
        $attribute->setName('');
        $this->addBlock($attribute);
        $this->setCaret($attribute->getCaret());
    }

    private function addChainToBlocks(int $pos, string $type): void
    {
        $chain = new ChainLinkBlock($pos, $type, $this);
        $chain->setChildIndex(\sizeof($this->getBlocks()));
        $this->addBlock($chain);
        $this->setCaret($chain->getCaret());
    }

    public function getBracketBlocks(): array
    {
        return $this->bracketBlocks;
    }

    protected function resolvePossibleTrigger(?int $start = null): void
    {
        if (\is_null($start)) {
            $start = $this->getCaret() + 1;
        }

        list($trigger, $pos) = $this->getNextLetter($start, self::$content);
        $res = ($this->triggerSwitch[$trigger] ?? $this->triggerSwitch['default']);
        $nextLetter   = self::$content->getLetter($pos + 1);
        $thridLetter  = self::$content->getLetter($pos + 2);
        $fourthLetter = self::$content->getLetter($pos + 3);

        $skipOperators = [
            "==" => true,
            "<=" => true,
            ">=" => true,
            "!=" => true,
        ];

        if (isset($skipOperators[$trigger . $nextLetter])) {
            // do nothing
        } elseif ($nextLetter == "=") {
            $this->addAttributeToBlocks($pos + 1);
        } elseif (Validate::isSymbol($nextLetter ?? '') && $thridLetter == "=") {
            $this->addAttributeToBlocks($pos + 2);
        } elseif (Validate::isSymbol($nextLetter ?? '') && Validate::isSymbol($thridLetter ?? '') && $fourthLetter == "=") {
            $this->addAttributeToBlocks($pos + 3);
        } elseif ($res) {
            $res($pos);
        }
    }

    public function recreate(): string
    {
        $subtype = $this->getSubtype();
        $script  = '';


        if ($subtype !== self::FIRST && !$this->isBracket) {
            $script .= '.';
        }

        $script .= $this->getInstruction();
        if ($this->isBracket) {
            $script .= "[";
            foreach ($this->getBracketBlocks() as $block) {
                $script .= $block->recreate();
            }
            $script .= "]";
        }

        foreach ($this->getBlocks() as $block) {
            $script .= $block->recreate();
        }

        if (!($this->getParent() instanceof ChainLinkBlock)) {
            return $script;
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
