<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log as Log, Exception as Exception, Contract as Contract, Validate as Validate};
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
            $letter = self::$content[$i];
            if ($letterFound && Validate::isWhitespace($letter) || $letterFound && $letter == '.') {
                $name = \mb_substr(self::$content, $i + 1, $start - $i);
                $start = $i + 1;
                break;
            }

            if (!Validate::isWhitespace($letter)) {
                $letterFound = true;
            }

            if ($i == 0) {
                $name = \mb_substr(self::$content, $i, $start);
                $start = 0;
            }
        }

        $end = $this->findVariableEnd($start);
        $value = \mb_substr(self::$content, $equalPos + 1, $end - ($equalPos + 1));
        $this->blocks = array_merge($this->blocks, $this->createSubBlocksWithContent($value));
        $this->setInstruction(\mb_substr(self::$content, $start, $end - $start))
            ->setInstructionStart($start)
            ->setName($name)
            ->setCaret($end);
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

        $last  = self::$content[$start - 1];
        $second = self::$content[$start - 2];
        $first = self::$content[$start - 3];
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
