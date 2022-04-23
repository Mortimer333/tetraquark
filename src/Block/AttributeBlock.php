<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\Log as Log;
use \Tetraquark\Contract as Contract;
use \Tetraquark\Abstract\VariableBlockAbstract as VariableBlock;

class AttributeBlock extends VariableBlock implements Contract\Block
{
    protected string $value = '';
    protected array $endChars = [
        ';' => true,
        "\n" => true
    ];

    protected array $instructionEnds = [
        '=' => true,
    ];
    /** @var string Holds value of operator before the equal sign (if one exists) for example `-` or `+` */
    protected string $augment = '';

    public function objectify(int $start = 0)
    {
        $letterFound = false;
        $end = $start;
        $start = $this->findAugment($start);

        for ($i=$start; $i >= 0; $i--) {
            $letter = self::$content[$i];
            if ($letterFound && $this->isWhitespace($letter)) {
                $start = $i;
                break;
            }

            if (!$this->isWhitespace($letter)) {
                $letterFound = true;
            }

            if ($i == 0) {
                $start = 0;
            }
        }
        $this->findInstructionEnd($end, $this->subtype, $this->instructionEnds);
        $this->blocks = array_merge($this->blocks, $this->createSubBlocks());
        if (\sizeof($this->blocks) == 0) {
            $instrEnd = $this->getInstructionStart() + \mb_strlen($this->getInstruction()) + 1;
            $this->setValue(trim(substr(self::$content, $instrEnd, $this->getCaret() - $instrEnd)));
        }
        $this->findAndSetName($this->getSubtype() . ' ', $this->instructionEnds);
    }

    /**
     * Finds what augment is before equal and returns its position
     * @param  int $start Equal sign position
     * @return int        The end of augment
     */
    protected function findAugment(int $start): int
    {
        $singleAugments = [
            '-' => true,
            '+' => true,
            '*' => true,
            '/' => true,
            '%' => true,
            '^' => true,
            '|' => true,
            '&' => true,
        ];

        $doubleAugments = [
            '>>' => true,
            '<<' => true,
            '**' => true,
        ];

        $tripleAugments = [
            '>>>' => true,
        ];

        $last  = self::$content[$start - 1];
        $second = self::$content[$start - 2];
        $first = self::$content[$start - 3];
        if ($singleAugments[$last] ?? false) {
            $start = $start - 1;
            $this->augment = $last;
        } elseif ($doubleAugments[$second . $last] ?? false) {
            $start = $start - 2;
            $this->augment = $second . $last;
        } elseif ($tripleAugments[$first . $second . $last] ?? false) {
            $start = $start - 3;
            $this->augment = $first . $second . $last;
        } else {
            $start = $start - 1;
        }
        return $start;
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
