<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate};
use \Tetraquark\Foundation\VariableBlockAbstract as VariableBlock;

class AttributeBlock extends VariableBlock implements Contract\Block
{
    protected array $endChars = [
        ';' => true,
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
        $name = '';

        for ($i=$start; $i >= 0; $i--) {
            $letter = self::$content->getLetter($i);
            if ($letterFound && Validate::isWhitespace($letter) || $letterFound && $letter == '.') {
                $name = self::$content->iSubStr($i + 1, $start - 1);
                $start = $i + 1;
                break;
            }

            if (!Validate::isWhitespace($letter)) {
                $letterFound = true;
            }

            if ($i == 0) {
                $name = self::$content->subStr($i, $start);
                $start = 0;
            }
        }

        $end = $this->findVariableEnd($equalPos);
        $value = self::$content->subStr($equalPos + 1, $end - ($equalPos + 1));
        $this->blocks = array_merge($this->blocks, $this->createSubBlocksWithContent($value));
        $this->setInstruction(self::$content->iCutToContent($start, $end - 1))
            ->setInstructionStart($start)
            ->setName($name)
            ->setCaret($end)
            ;
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
            '&&' => true,
            '||' => true,
            '??' => true,
        ];

        $tripleAugments = [
            '>>>' => true,
        ];

        $last  = self::$content->getLetter($start - 1);
        $second = self::$content->getLetter($start - 2);
        $first = self::$content->getLetter($start - 3);
        if ($tripleAugments[$first . $second . $last] ?? false) {
            $start = $start - 4;
            $this->augment = $first . $second . $last;
        } elseif ($doubleAugments[$second . $last] ?? false) {
            $start = $start - 3;
            $this->augment = $second . $last;
        } elseif ($singleAugments[$last] ?? false) {
            $start = $start - 2;
            $this->augment = $last;
        } else {
            $start = $start - 1;
        }
        return $start;
    }

    public function recreate(): string
    {
        $script = $this->getAlias($this->getName());

        if (\sizeof($this->getBlocks()) > 0 || \mb_strlen($this->getValue()) > 0) {
            $script .= $this->augment . '=';
        }

        foreach ($this->getBlocks() as $block) {
            $script .= rtrim($block->recreate(), ';');
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
