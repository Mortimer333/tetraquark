<?php declare(strict_types=1);

namespace Tetraquark;

/**
 *  Class containing script in form of sliced string into UTF-8 letters for easier navigation
 *  and memory managment
 */
class Content
{
    /**
     * To avoid creating at some point two variable which would hold whole script files
     * we are using pointer to indicate which version of content we are currently using
     * @var int $contentPointer
     */
    private int $contentPointer = -1;

    /**
     * Stores all existing versions of content. We keep view versions so we don't accidently
     * overload the memory when trying to replace content for few operations. It also holds
     * the size of the content.
     * Structure:
     * [
     *   [
     *     "content" => ['a','b','õ'],
     *     "size" => 3
     *   ]
     * ]
     * @var array
     */
    private array $contents = [];

    public function __construct(string $content)
    {
        $this->cutAndAddContent($content);
    }

    /**
     * Cuts string into UTF-8 letters keeping it simple and easy to get next letter
     * @param string  $content  Script
     * @param boolean $replace  If to replace current version with new content
     */
    private function cutAndAddContent(string $content, bool $replace = false): void
    {
        if (!$replace) {
            $this->contentPointer++;
        }

        $this->contents[$this->contentPointer] = [];
        $this->contents[$this->contentPointer]['content'] = Str::iterate(
            $content, 0, [[]],
            function (string $letter, int $i, array &$content)
            {
                $content[] = $letter;
                return $content;
            }
        );
        $this->contents[$this->contentPointer]['size'] = \sizeof($this->contents[$this->contentPointer]['content']);
    }

    /**
     * Clear contents and set contentPointer to default
     */
    private function clear(): void
    {
        $this->contents = [];
        $this->contentPointer = -1;
    }

    /**
     * Adds new content
     * @param  string  $content Script
     * @param  boolean $clear   If set to `true` will remove all old versions
     * @return self
     */
    public function setContent(string $content, bool $clear = false, bool $replace = false): self
    {
        if (!$replace && $clear) {
            $this->clear();
        }
        $this->cutAndAddContent($content, $replace);
        return $this;
    }

    public function setArrayContent(array $content, bool $clear = false, bool $replace = false): self
    {
        if (!$replace && $clear) {
            $this->clear();
        } elseif (!$replace) {
            $this->contentPointer++;
        }

        $this->contents[$this->contentPointer] = [
            'content' => $content,
            'size'    => \sizeof($content),
        ];
        return $this;
    }

    /**
     * Returns current version of content array
     * @return array Content in form of an array
     */
    public function getContent(): array
    {
        return $this->contents[$this->contentPointer];
    }

    /**
     * Replaces current content with new one
     * @param  string $content Script
     * @return self
     */
    public function replaceContent(string $content): self
    {
        $this->cutAndAddContent($content, true);
        return $this;
    }

    /**
     * Returns current content size
     * @return int Size of current content
     */
    public function getLength(): int
    {
        return $this->contents[$this->contentPointer]['size'];
    }

    /**
     * Returns letter from current content
     * @param  int    $pos Index of letter
     * @return null|string Will return null if letter was not found
     */
    public function getLetter(int $pos): ?string
    {
        return $this->contents[$this->contentPointer]['content'][$pos] ?? null;
    }

    /**
     * Cuts content and joins all items to return string
     * @param  int      $start  From where start the cut
     * @param  null|int $length How long the string should be
     * @return string
     */
    public function subStr(int $start, ?int $length): string
    {
        $cut = array_slice($this->contents[$this->contentPointer]['content'], $start, $length);
        return implode('', $cut);
    }

    /**
     * Same as subStr but work on indexes not on length
     * @param  int    $start Where to start the cut
     * @param  int    $end   Index of where to end it
     * @return string
     */
    public function iSubStr(int $start, int $end): string
    {
        $cut = array_slice($this->contents[$this->contentPointer]['content'], $start, $end - $start);
        return implode('', $cut);
    }

    /**
     * Removes current content and decreases contentPointer
     * @return self
     */
    public function removeContent(): self
    {
        unset($this->contents[$this->contentPointer]);
        $this->contentPointer--;
        return $this;
    }

    /**
     * Similarly to subStr but it returns Content
     * @param  int     $start
     * @param  int     $length
     * @return Content
     */
    public function cutToContent(int $start, int $length): Content
    {
        $cut = array_slice($this->contents[$this->contentPointer]['content'], $start, $length);
        return (new Content(''))->setArrayContent($cut, true);
    }

    /**
     * Similarly to iSubStr but it returns Content
     * @param  int     $start
     * @param  int     $end
     * @return Content
     */
    public function iCutToContent(int $start, int $end): Content
    {
        $cut = array_slice($this->contents[$this->contentPointer]['content'], $start, $end - $start);
        return (new Content(''))->setArrayContent($cut, true);
    }

    public function trim($letter = "\s"): Content
    {
        $start = null;
        $end   = null;
        for ($i=0; $i < $this->getLength(); $i++) {
            $letter = $this->getLetter($i);
            if (preg_match('/' . $letter . '/', $letter) === false) {
                $start = $i;
            }
        }

        for ($i=$this->getLength(); $i >= 0; $i-) {
            $letter = $this->getLetter($i);
            if (preg_match('/' . $letter . '/', $letter) === false) {
                $end = $i;
            }
        }
        return $this->iCutToContent($start, $end);
    }
}
