<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Content};
use \Tetraquark\Foundation\BlockAbstract as Block;

class BracketChainLinkBlock extends Block implements Contract\Block
{
    /** @var AttributeBlock Holder for Attribute Block which will hold assigned values */
    protected AttributeBlock $variable;
    protected const VARIABLE = 'variable';
    protected const METHOD   = 'method';
    public const BRACKET_BLOCK_CREATE = 'create_bracket_blocks';
    protected array $bracketBlocks = [];
    protected Contract\Block $methodValues;
    protected string $identifier = '';

    public function objectify(int $start = 0)
    {
        $this->setInstruction(new Content(''));
        $this->setInstructionStart($start);
        $this->setName('');
        $end = $start;
        $string = false;
        $template = false;
        $stringEnd = false;
        $getSubBlocks = false;
        $checkFirsLetter = true;
        $start += 1;
        for ($i=$start; $i < self::$content->getLength(); $i++) {
            $letter = self::$content->getLetter($i);
            $end = $i + 1;
            if (Validate::isWhitespace($letter)) {
                continue;
            }

            // If first letter is not string landmark then start gathering sub blocks
            if ($checkFirsLetter) {
                if ($letter != '"' && $letter != "'" && $letter != '`') {
                    $getSubBlocks = true;
                    $end = $i;
                    break;
                } else {
                    $checkFirsLetter = false;
                }
            }

            if ($template && $this->startsTemplateLiteralVariable($letter, self::$content, $i)) {
                $getSubBlocks = true;
                $end = $start;
                $string = false;
                $template = false;
                break;
            }

            // Possible syntaxes:
            // - ['property'] - replace `property` with alias
            // - ['proper' + int] - replace only int
            // - [`proper${int}`] - again replace int
            // - [`property`] - replace `property` with alias
            // - ["property"] - replace `property` with alias
            // - ["propert" + func(str)] - replace func and str
            // - [func(str) + "propert"] - replace func and str
            // Solution: check if string is the only part of this call. If not start getting subblocks after it ended

            if (!$string && !$template && !$stringEnd) {
                if ($letter == "'" || $letter == '"') {
                    $string = true;
                    continue;
                }

                if ($letter == '`') {
                    $template = true;
                    continue;
                }
            } elseif (!$stringEnd && $string && Validate::isStringLandmark($letter, self::$content->getLetter($i - 1), true)) {
                $stringEnd = true;
                continue;
            } elseif (!$stringEnd && $template && Validate::isTemplateLiteralLandmark($letter, self::$content->getLetter($i - 1), true)) {
                $stringEnd = true;
                continue;
            }

            if ($letter == ']' || $stringEnd) {
                $end = $i;
                if ($stringEnd && $letter != ']') {
                    $string = false;
                    $template = false;
                    $getSubBlocks = true;
                    $end = $start;
                }
                break;
            }
        }
        $this->setCaret($end);

        if ($getSubBlocks) {
            $oldEndChars = $this->endChars;
            $oldSubtype  = $this->getSubtype();
            $this->endChars = [
                ']' => true
            ];
            $this->setSubtype(self::BRACKET_BLOCK_CREATE);
            $this->bracketBlocks = $this->createSubBlocks();
            foreach ($this->bracketBlocks as &$block) {
                $block->setPlacement('getBracketBlocks');
            }
            $this->setSubtype($oldSubtype);
            $this->endChars = $oldEndChars;
            $this->setCaret($this->getCaret() + 1);
        }

        list($nextLetter, $pos) = $this->getNextLetter($this->getCaret() + 1, self::$content);

        if ($nextLetter == '(') {
            $this->setSubtype(self::METHOD);
            $this->methodValues = new CallerBlock($pos, '', $this);
            $this->methodValues->setChildIndex(0);
            $this->setCaret($this->methodValues->getCaret() + 1);
            $this->blocks = array_merge($this->blocks, $this->createSubBlocks(onlyOne: true));
        } elseif ($nextLetter == '=' && self::$content->getLetter($pos + 1) != '=') {
            $this->setSubtype(self::VARIABLE);
            $this->methodValues = new AttributeBlock($pos, '', $this);
            $this->methodValues->setChildIndex(0);
            $this->methodValues->setName('');
            $this->setCaret($this->methodValues->getCaret() + 1);
        } else {
            if (!$getSubBlocks && self::$content->getLetter($this->getCaret()) == ']') {
                $this->setCaret($this->getCaret() + 1);
            }

            $possibleOperation = $nextLetter . self::$content->getLetter($pos + 1);
            if ($possibleOperation === '--' || $possibleOperation == '++') {
                $symbol = new SymbolBlock($pos + 1, $possibleOperation, $this);
                $symbol->setChildIndex(0);
                $this->setBlocks([$symbol]);
                $this->setCaret($pos + 1);
            } else {
                $this->blocks = array_merge($this->blocks, $this->createSubBlocks(onlyOne: true));
            }
        }


        $name = trim(self::$content->iSubStr($start, $end - 1));
        if ($string) {
            $name = \mb_substr($name, 1, -1);
            if ($this->getSubtype() === self::VARIABLE) {
                $this->setName($name);
            }
            $this->setIdentifier($name);
        } elseif ($template) {
            if (strpos($name, '${') === false) {
                $name = \mb_substr($name, 1, -1);
                if ($this->getSubtype() === self::VARIABLE) {
                    $this->setName($name);
                }
                $this->setIdentifier($name);
            }
        }
    }

    public function recreate(): string
    {
        $name = $this->getIdentifier();
        $script = '[';
        if (\mb_strlen($name)) {
            $script .= "'" . $this->replaceVariablesWithAliases(new Content($name)) . "'";
        } else {
            foreach ($this->bracketBlocks as $block) {
                $script .= rtrim($block->recreate(), ';');
            }
        }

        $script .= ']';

        if ($this->getSubtype() == self::METHOD || $this->getSubtype() == self::VARIABLE) {
            $script .= rtrim($this->methodValues->recreate(), ';');
        }

        foreach ($this->getBlocks() as $block) {
            if ($block::class === SymbolBlock::class) {
                $script .= rtrim($block->recreate(), ';');
            } else {
                $script .= '.' . rtrim($block->recreate(), ';');
            }
        }
        if (!$this->isNextSiblingContected()) {
            return $script . ';';
        }
        return $script;
    }

    public function getMethodValues(): ?Contract\Block
    {
        return $this->methodValues ?? null;
    }

    public function getBracketBlocks(): array
    {
        return $this->bracketBlocks;
    }

    protected function setIdentifier(string $id): self
    {
        $this->identifier = $id;
        return $this;
    }

    protected function getIdentifier(): string
    {
        return $this->identifier;
    }
}
