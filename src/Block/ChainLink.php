<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\Log as Log;
use \Tetraquark\Contract as Contract;
use \Tetraquark\Block as Block;

class ChainLink extends Block implements Contract\Block
{
    protected const FIRST = 'first';
    protected const MIDDLE = 'middle';
    protected const END_METHOD = 'end:method';
    protected const END_VARIABLE = 'end:variable';
    public function __construct(
        int $start,
        protected string $subtype = '',
        protected array  $data  = []
    ) {
        parent::__construct($start, $subtype, $data);
    }
    public function objectify(int $start = 0)
    {
        $this->setName('');
        $endChars = array_merge([';' => true], $this->special);

        if ($this->getSubtype() == self::FIRST) {
            $this->findInstructionStart($start, $endChars);
            return;
        }

        $end = null;
        $startLetterSearch = false;
        Log::log('Start search for end of link', 1);
        Log::increaseIndent();
        for ($i=$start; $i < \mb_strlen(self::$content); $i++) {
            $letter = self::$content[$i];
            Log::log('Letter: ' . $letter, 3);
            if ($endChars[$letter] ?? false || $startLetterSearch && !$this->isWhitespace($letter)) {
                Log::log('End found: ' . $letter, 1);
                $end = $i;
                if ($letter == '=') {
                    $this->setSubtype(self::END_VARIABLE);
                } elseif ($letter == '(') {
                    $this->setSubtype(self::END_METHOD);
                    $i += 2;
                } elseif ($letter == '.') {
                    $this->setSubtype(self::MIDDLE);
                }

                $this->setCaret($i - 1);
                break;
            }

            if ($this->isWhitespace($letter)) {
                $startLetterSearch = true;
            }
        }
        Log::decreaseIndent();

        $instruction = \mb_substr(self::$content, $start, $end - $start);
        Log::log('Instruction: ' . $instruction, 1);
        $instrLen = \mb_strlen($instruction);
        $this->setInstructionStart($start)
            ->setInstruction($instruction);

        if ($this->getSubtype() == self::END_METHOD) {
            $this->endChars = [
                ')' => true
            ];
            $this->createSubBlocks();
        }
    }

    public function recreate(): string
    {
        $script = $this->replaceVariablesWithAliases($this->getInstruction());

        if ($this->getSubtype() == self::END_METHOD) {
            $script .= "(";
        }

        if ($this->getSubtype() == self::MIDDLE || $this->getSubtype() == self::FIRST) {
            $script .= '.';
        }

        foreach ($this->getBlocks() as $block) {
            $script .= rtrim($block->recreate(), ';');
        }

        if ($this->getSubtype() == self::END_METHOD) {
            $script .= ");";
        }

        return $script;
    }
}
