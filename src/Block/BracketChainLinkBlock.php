<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Content};
use \Tetraquark\Foundation\BlockAbstract as Block;

class BracketChainLinkBlock extends Block implements Contract\Block
{
    protected array $endChars = [
        ']' => true
    ];
    /** @var AttributeBlock Holder for Attribute Block which will hold assigned values */
    protected AttributeBlock $variable;
    protected const VARIABLE = 'variable';

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
            $end = $i;
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
            $this->blocks = array_merge($this->blocks, $this->createSubBlocks());
        }

        for ($i=$this->getCaret() + 1; $i < self::$content->getLength(); $i++) {
            $letter = self::$content->getLetter($i);
            if (Validate::isWhitespace($letter)) {
                continue;
            }

            if ($letter != '=' || $letter == '=' && self::$content->getLetter($i + 1) == '=') {
                if (!$getSubBlocks) {
                    $this->setCaret($start);
                    $this->blocks = array_merge($this->blocks, $this->createSubBlocks());
                }
                return;
            } else {
                $this->setSubtype(self::VARIABLE);
                $attribute = new AttributeBlock($i);
                $attribute->setName('');
                $this->variable = $attribute;
                $this->setCaret($attribute->getCaret());
                break;
            }
        }

        $name = trim(self::$content->iSubStr($start, $end));
        if ($string) {
            $name = \mb_substr($name, 1, -1);
            $this->setName($name);
        } elseif ($template) {
            if (strpos($name, '${') === false) {
                $name = \mb_substr($name, 1, -1);
                $this->setName($name);
            }
        }
    }

    public function recreate(): string
    {
        $name = $this->getName();
        $script = '[';
        if (\mb_strlen($name)) {
            $script .= "'" . $this->replaceVariablesWithAliases($name) . "'";
        } else {
            foreach ($this->getBlocks() as $block) {
                $script .= rtrim($block->recreate(), ';');
            }
        }

        $script .= ']';

        if ($this->getSubtype() === self::VARIABLE) {
            $script .= rtrim($this->variable->recreate(), ';') . ';';
        }

        return $script;
    }
}
