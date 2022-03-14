<?php declare(strict_types=1);

namespace Tetraquark;

abstract class Block
{
    protected string $content;
    protected string $subtype;
    protected array  $data;
    /** @var Block[] Array of Blocks */
    protected array  $blocks;

    public function __construct(
        string $content,
        array $data  = []
    ) {
        $this->content = $content;
        $this->data    = $data;
        $this->objectify();
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
        $endChars = [
            "\n" => true,
            ";" => true,
            // "}" This is also end letter but only for functions and classes so we will check this later
        ];
        return $endChars[$letter] ?? false;
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

    protected function BlockFactory(string $name, string $content): Block
    {
        $blocks = [
            'function' => 'Tetraquark\Block\Method',
            '=>'       => 'Tetraquark\Block\ArrowMethod',
        ];
        if (!isset($blocks[$name])) {
            throw new Exception("Block couldn't be created with name: " . htmlspecialchars($name), 404);
        }
        return new $blocks[$name]($content);
    }
}
