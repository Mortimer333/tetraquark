<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Str, Content};
use \Tetraquark\Foundation\BlockAbstract as Block;

class ScriptBlock extends Block implements Contract\Block
{
    public const DUMMY_PATH = 'test';
    protected array  $endChars = [];
    /** @var string Minified script */
    protected string $minified = '';

    public function __construct(
        protected string $path,
        int    $start = 0,
        string $subtype = '',
    ) {
        $this->setChildIndex(0);
        if (!isset(self::$mainScript)) {
            self::$mainScript = $this;
        }

        if ($path == self::DUMMY_PATH) {
            if (isset(self::$content)) {
                self::$content->addContent('');
            } else {
                self::$content = new Content('');
            }
        } else {
            $content = Str::getFile($path);
            // Adding space at the start for any Blocks that require space before their keys
            $content = $this->generateContent(' ' . $content);
            $content = $this->removeComments($content);
            if (isset(self::$content)) {
                self::$content->addArrayContent($content->getContent());
            } else {
                self::$content = $content;
            }
        }
        parent::__construct($start, $subtype);
        self::$folder->addFile($path, $this);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function objectify(int $start = 0)
    {
        $this->setName('');
        Log::timerStart();
        Log::log("Mapping...");
        $this->map();
        Log::log("=======================");
        Log::log("Creating aliases...");
        // $this->generateAliases(); -- work in progress, see TODO
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
        self::$content->removeContent();
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
        $content = preg_replace('/[ \t]+/', ' ', $content);
        // Change space + new line (` \n`) to just new line
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
        if (self::$mainScript === $this) {
            $script .= self::$import->recreate();
        }
        foreach ($this->blocks as $block) {
            $script .= $block->recreate();
        }
        
        return $this->fixScript(new Content($script));
    }

    public function recreateSkip(array $classNamesSwitch): string
    {
        $script = '';
        foreach ($this->blocks as $block) {
            if ($classNamesSwitch[$block::class] ?? false) {
                continue;
            }

            $script .= $block->recreate();
        }
        return $this->fixScript(new Content($script));
    }
}
