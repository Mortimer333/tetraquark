<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate};
use \Tetraquark\Foundation\VariableBlockAbstract;

class VariableBlock extends VariableBlockAbstract implements Contract\Block
{
    protected string $value = '';
    protected array $endChars = [
        ';' => true,
        "\n" => true
    ];

    protected array $instructionEnds = [
        '=' => true,
        ';' => true,
    ];

    /** @var bool Is this var a multi definition (var a,b,c;) */
    protected bool $multiDef = false;

    public function objectify(int $start = 0)
    {
        $this->setInstructionStart($start - \mb_strlen($this->getSubtype()));
        /**
         * Possible combinations:
         * var a = 1;
         * var a;
         * var a, b, c;
         * var a, b = 2;
         * var a = 2
         * +2;
         * var a = 2 +
         * 2
         * var a = 1
         * var a = b +1 + c['f'] +h.f.v + e()
         * var a = b +1 + c['f'] +h.f.v + e(2,1), b = 'c'
         */

        $end   = $this->findVariableEnd($start);
        $items = $this->seperateVariableItems($start, $end);
        $this->addVariableItems($items);

        $this->setName('');
        $this->setInstruction(self::$content->iCutToContent($this->getInstructionStart(), $end));
        $this->setCaret($end);
    }

    public function setValue(string $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
