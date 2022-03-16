<?php declare(strict_types=1);

namespace Tetraquark;

abstract class Block
{
    protected string $content;
    /** @var string Blocks instruction - declaration of variable, functions definition etc. */
    protected string $instruction;
    protected string $subtype;
    protected array  $data;
    /** @var Block[] Array of Blocks */
    protected array  $blocks;
    protected array  $endChars = [
        "\n" => true,
        ";" => true,
        // "}" This is also end letter but only for functions and classes so we will check this later
    ];

    public function __construct(
        string $content,
        int    $start = 0,
        array  $data  = []
    ) {
        $this->content = $content;
        $this->data    = $data;
        $this->objectify($start);
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    protected function addWord(array &$item, string &$contents, int &$i): void
    {
        $content = trim(trim(substr($contents, 0, $i + 1)), ';');
        if (\strlen($content) > 0) {
            $item[] = $content;
        }
        $contents = substr($contents, $i + 1);
        $i = -1;
    }

    protected function isEndChar(string $letter): bool
    {
        return $this->endChars[$letter] ?? false;
    }

    protected function isNewBlock(string $letter, int $i, string $content)
    {
        $hints = [
            "n" => 'function',
            ">" => '=>',
        ];
        $searchName = $hints[$letter] ?? false;
        $searchLen = \strlen($searchName ?: '');
        if (!$searchName || $i < ($searchLen - 1)) {
            return false;
        }
        $possibleName = substr($content, $i - ($searchLen - 1), $searchLen);
        if ($possibleName === $searchName) {
            return $possibleName;
        }
    }

    protected function BlockFactory(string $name, string $content, int $start): Block
    {
        $blocks = [
            'function' => 'Tetraquark\Block\Method',
            '=>'       => 'Tetraquark\Block\ArrowMethod',
        ];
        if (!isset($blocks[$name])) {
            throw new Exception("Block couldn't be created with name: " . htmlspecialchars($name), 404);
        }
        return new $blocks[$name]($content, $start);
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
}
