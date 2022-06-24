<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate};
use \Tetraquark\Foundation\BlockAbstract;

class SwitchCaseBlock extends BlockAbstract implements Contract\Block
{
    public const DEFAULT = 'switch-case:default';
    protected $caseValue = '';
    protected array $endChars = [];

    public function objectify(int $start = 0)
    {
        $this->setName('');
        $this->findInstructionEnd($start, $this->getSubtype(), [':' => true]);
        if (substr($this->getSubtype(), 0, -1) === 'default') {
            $this->setSubtype(self::DEFAULT);
        } else {
            $this->setCaseValue($this->getInstruction()->subStr(4));
        }
        $this->blocks = $this->createSubBlocks($this->getCaret() + 1, endMethod: function (int &$i, string $letter, string $undefined) {
            if ($letter === '}') {
                $i--;
                return true;
            }

            if ($letter === 'c') {
                $caseCheck = self::$content->subStr($i, 5);
                if ($caseCheck === 'case ' || $caseCheck === 'case:' || $caseCheck === "case\n") {
                    $i--;
                    return true;
                }
            }

            if ($letter === 'd') {
                $caseCheck = self::$content->subStr($i, 8);
                if ($caseCheck === 'default ' || $caseCheck === 'default:' || $caseCheck === "default\n") {
                    $i--;
                    return true;
                }
            }
        });
    }

    public function recreate(): string
    {
        if ($this->getSubtype() === self::DEFAULT) {
            $script = 'default:';
        } else {
            $script = 'case ' . $this->getCaseValue() . ':';
        }
        
        foreach ($this->getBlocks() as $block) {
            $script .= $block->recreate();
        }
        return $script;
    }

    public function getCaseValue(): string
    {
        return $this->caseValue;
    }

    protected function setCaseValue(string $value): self
    {
        $this->caseValue = trim($value);
        return $this;
    }
}
