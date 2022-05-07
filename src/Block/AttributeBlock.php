<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log as Log, Exception as Exception, Contract as Contract, Validate as Validate};
use \Tetraquark\Foundation\VariableBlockAbstract as VariableBlock;

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
        $equalPos = $start;
        $start = $this->findAugment($start);
        $name = null;

        for ($i=$start - 1; $i >= 0; $i--) {
            $letter = self::$content[$i];
            if ($letterFound && Validate::isWhitespace($letter)) {
                $name = \mb_substr(self::$content, $i, $start - 1 - $i);
                $start = $i;
                break;
            }

            if (!Validate::isWhitespace($letter)) {
                $letterFound = true;
            }

            if ($i == 0) {
                $name = \mb_substr(self::$content, $i, $start - 1 - $i);
                $start = 0;
            }
        }
        // $this->findInstructionEnd($start, $this->subtype, $this->instructionEnds);
        $end = $this->findVariableEnd($start);
        $value = \mb_substr(self::$content, $equalPos + 1, $end - ($equalPos + 1));
        $this->blocks = array_merge($this->blocks, $this->createSubBlocksWithContent($value));
        $this->setInstruction(\mb_substr(self::$content, $start, $end - $star))
            ->setInstructionStart($start)
            ->setName($name)
            ->setCaret($end);
            ;
        // if (\sizeof($this->blocks) == 0) {
        //     $instrEnd = $this->getInstructionStart() + \mb_strlen($this->getInstruction()) + 1;
        //     $this->setValue(trim(\mb_substr(self::$content, $instrEnd, $this->getCaret() - $instrEnd)));
        // }

        // $this->findAndSetName($this->getSubtype() . ' ', $this->instructionEnds);

        // if (\mb_strlen($this->augment) > 0) {
        //     $this->setName(\mb_substr($this->getName(), 0, -\mb_strlen($this->augment)));
        // }
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

    public function recreate(): string
    {
        $script = $this->getSubType() . ' ' . $this->getAlias($this->getName());

        if (\sizeof($this->getBlocks()) > 0 || \mb_strlen($this->getValue()) > 0) {
            $script .= $this->augment . '=';
        }

        foreach ($this->getBlocks() as $block) {
            $script .= $block->recreate();
        }

        $value = $this->getValue();
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
        return $script;
    }
}
