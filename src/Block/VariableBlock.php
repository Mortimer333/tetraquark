<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log as Log, Exception as Exception, Contract as Contract, Validate as Validate};
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
        $contentHolder = self::$content;
        foreach ($items as $item) {
            $item = preg_replace('/[\n]/', ' ', $item);
            $item = preg_replace('/[ \t]+/', ' ', $item) . ';';
            self::$content = $item;
            $this->blocks[] = new VariableItemBlock();
        }
        self::$content = $contentHolder;

        $this->setName('');
        $this->setInstruction('');
        $this->setCaret($end);
        return;

        $multiDefinition = false;
        $placeholder = false;
        $setter = false;
        // Script has fixed the file so between variable instruction and variable name is single space
        // which means we can start from the name and search for it end
        for ($i=$start + 2; $i < \mb_strlen(self::$content); $i++) {
            $letter = self::$content[$i];

            if (Validate::isWhitespace($letter) || $letter === '=' || $letter === ',' || $letter === ';') {
                if (Validate::isWhitespace($letter)) {
                    list($letter) = $this->getNextLetter($i, self::$content);
                }

                if ($letter === ',') {
                    $multiDefinition = true;
                } elseif ($letter === '=') {
                    $setter = true;
                } else {
                    $placeholder = true;
                }

                $end = $i - 1;
                break;
            }
        }

        $this->setInstruction('');
        $this->setName(\mb_substr(self::$content, $start + 2, $end));

        if ($placeholder) {
            return;
        }

        if ($multiDefinition) {
            $this->multiDef = true;
            $this->handleMultiDefinition($end);
            return;
        }

        $this->findInstructionEnd($start, $this->subtype, $this->instructionEnds);

        $this->blocks = array_merge($this->blocks, $this->createSubBlocks());
        if (\sizeof($this->blocks) == 0) {
            $instrEnd = $this->getInstructionStart() + \mb_strlen($this->getInstruction());
            $this->setValue(trim(substr(self::$content, $instrEnd, $this->getCaret() - $instrEnd)));
        }
        $this->findAndSetName($this->getSubtype() . ' ', $this->instructionEnds);
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

    protected function handleMultiDefinition(int $nameEnd): void
    {

    }
}
