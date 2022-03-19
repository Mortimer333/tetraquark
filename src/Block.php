<?php declare(strict_types=1);

namespace Tetraquark;
use \Xeno\X as Xeno;

abstract class Block
{
    static protected string $content;
    protected int    $caret = 0;
    protected bool   $endFunction = false;
    /** @var Xeno $instruction Actual block representation in code */
    protected Xeno   $instruction;
    protected string $subtype = '';
    protected array  $data;
    /** @var Block[] $blocks Array of Blocks */
    protected array  $blocks = [];
    protected array  $endChars = [
        "\n" => true,
        ";" => true,
    ];

    public function __construct(
        string $content,
        int    $start = 0,
        string $subtype = '',
        array  $data  = []
    ) {
        self::$content = $content;
        $this->subtype = $subtype;
        $this->data    = $data;
        $this->objectify($start);
    }

    public function getContent(): string
    {
        return self::$content;
    }

    public function setContent(string $content): self
    {
        self::$content = $content;
        return $this;
    }

    public function getCaret(): int
    {
        return $this->caret;
    }

    public function setCaret(int $caret): self
    {
        $this->caret = $caret;
        return $this;
    }

    public function getSubtype(): string
    {
        return $this->subtype;
    }

    public function setSubtype(string $subtype): self
    {
        $this->subtype = $subtype;
        return $this;
    }

    public function getInstruction(): Xeno
    {
        return $this->instruction;
    }

    public function setInstruction(Xeno $instruction): self
    {
        $this->instruction = $instruction;
        $this->instruction->preg_replace('!\s+!', ' ');
        return $this;
    }

    protected function isEndChar(string $letter): bool
    {
        return $this->endChars[$letter] ?? false;
    }

    protected function isNewBlock(string $name)
    {
        $hints = [
            'function' => 'function',
            '=>'       => '=>',
            'let'      => 'let',
            'const'    => 'const',
            'var'      => 'var'
        ];
        $name = $hints[$name] ?? false;
        return $name;
    }

    protected function BlockFactory(string $name, string $content, int $start): Block
    {
        $prefix = 'Tetraquark\Block\\';
        $blocks = [
            'function' => 'Method',
            '=>'       => 'ArrowMethod',
            'let'      => 'Variable',
            'const'    => 'Variable',
            'var'      => 'Variable'
        ];
        if (!isset($blocks[$name])) {
            throw new Exception("Block couldn't be created with name: " . htmlspecialchars($name), 404);
        }
        $class = $prefix . $blocks[$name];
        return new $class($content, $start, $name);
    }

    protected function isValidVariable(string $variable): bool
    {
        $regex = '/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\u200C\u200D]*+$/';
        $res = preg_match($regex, $variable);
        if (!$res) {
            return false;
        }

        $notAllowedConsts = [
            'break' => true, 'do' => true, 'instanceof' => true,
            'typeof' => true, 'case' => true, 'else' => true, 'new' => true,
            'var' => true, 'catch' => true, 'finally' => true, 'return' => true,
            'void' => true, 'continue' => true, 'for' => true, 'switch' => true,
            'while' => true, 'debugger' => true, 'function' => true, 'this' => true,
            'with' => true, 'default' => true, 'if' => true, 'throw' => true,
            'delete' => true, 'in' => true, 'try' => true, 'class' => true,
            'enum' => true, 'extends' => true, 'super' => true, 'const' => true,
            'export' => true, 'import' => true, 'implements' => true, 'let' => true,
            'private' => true, 'public' => true, 'yield' => true, 'interface' => true,
            'package' => true, 'protected' => true, 'static' => true, 'null' => true,
            'true' => true, 'false' => true
        ];
        return !isset($notAllowedConsts[$variable]);
    }

    protected function isWhitespace(string $letter): bool
    {
        return (bool) preg_match('/[\s]/', $letter);
    }

    protected function findInstructionEnd(int $start, string $name, array $endChars): void
    {
        $properEnd = null;
        for ($i=$start + 1; $i < strlen(self::$content); $i++) {
            $letter = self::$content[$i];
            Log::log("Letter: " . $letter, 2);
            if ($endChars[$letter] ?? false) {
                Log::log("Proper end : " . $i, 2);
                $properEnd = $i + 1;
                $this->setCaret($properEnd);
                break;
            }
        }

        $properStart = $start - (strlen($name) - 1);

        $instruction = (new Xeno(self::$content))->substr($properStart, $properEnd - $properStart);
        $this->setInstruction($instruction);
    }

    protected function constructBlock(string $word, int &$i): ?Block
    {
        if (!($name = $this->isNewBlock($word))) {
            return null;
        }

        Log::increaseIndent();
        Log::log("New block: " . $name);

        $block = $this->blockFactory($name, self::$content, $i);

        Log::log('Iteration count changed from ' . $i . " to " . $block->getCaret(), 1);
        Log::log("Instruction: `". $block->getInstruction() . "`");

        $i = $block->getCaret();
        Log::decreaseIndent();
        return $block;
    }

    protected function createSubBlocks(): void
    {
        $word = '';
        for ($i=$this->getCaret(); $i < \strlen(self::$content); $i++) {
            $letter = self::$content[$i];
            $word  .= $letter;
            Log::log("Letter: " . $letter . ', Word: ' . $word, 2);
            if ($this->isWhitespace($letter) && !($this->endChars[$letter] ?? false)) {
                Log::log("Word clear!", 2);
                $word = '';
                continue;
            }

            Log::log("End Check: " . implode(', ', array_keys($this->endChars)) . ' == ' . $letter, 1);
            if ($this->endChars[$letter] ?? false) {
                Log::log("End found!", 1);
                break;
            }

            $block = $this->constructBlock($word, $i);
            if ($block) {
                Log::log("Add Block!", 1);
                $this->blocks[] = $block;
                $word = '';
            }
        }

        $this->setCaret($i);
        Log::log("Updated caret " . $this->getCaret(), 1);

    }
}
