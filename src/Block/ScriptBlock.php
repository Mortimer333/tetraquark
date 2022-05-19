<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Str, Content};
use \Tetraquark\Foundation\BlockAbstract as Block;

class ScriptBlock extends Block implements Contract\Block
{
    protected array  $endChars = [];
    /** @var string Minified script */
    protected string $minified = '';

    public function __construct(
        string $content,
        protected int    $start = 0,
        protected string $subtype = '',
        protected array  $data  = []
    ) {
        self::$content = $this->generateContent($content);
        parent::__construct($start, $subtype, $data);
    }

    public function objectify(int $start = 0)
    {
        $this->setName('');
        Log::timerStart();
        Log::log("Mapping...");
        $this->map();
        Log::log("=======================");
        Log::log("Creating aliases...");
        $this->generateAliases();
        Log::log("=======================");
        $aliasesStr = "Aliases: ";
        foreach (self::$mappedAliases as $key => $value) {
            $aliasesStr .= "$key => $value, ";
        }
        Log::log($aliasesStr);
        Log::log("Recreating...");
        $this->setMinified($this->recreate());
        Log::log("=======================");
        Log::displayBlocks($this->blocks);
        Log::timerEnd();
    }

    protected function generateContent(string $content): Content
    {
        $content = $this->prepare($content);
        return new Content($content);
    }

    protected function prepare(string $content): string
    {
        $content = str_replace("\r","\n", $content);
        $content = preg_replace('/[\n]+/',"\n", $content);
        // Change space + new line (` \n`) to just new line
        $content = preg_replace('/[ \t]+/', ' ', $content);
        $content = preg_replace('/ \n+/',"\n", $content);
        // This fixes all problem with prototypes that are moved to new line with trailing dot (obj\n.attr)
        $content = preg_replace('/\n\./', '.', $content);
        return $content;
    }

    protected function map(): void
    {
        $this->setCaret(0);
        $this->blocks = array_merge($this->blocks, $this->createSubBlocks());
    }

    protected function setMinified(string $minified): self
    {
        $this->minified = $minified;
        return $this;
    }

    public function getMinified(): string
    {
        return $this->minified;
    }

    public function recreate(): string
    {
        $script = '';
        foreach ($this->blocks as $block) {
            $script .= $block->recreate();
        }
        return $this->fixScript(new Content($script));
    }
}
