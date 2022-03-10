<?php declare(strict_types=1);

namespace Tetraquark;

class Tetraquark
{
    /**
     * Minifier settings. No option is require and default looks like this:
     *  - only-nested=true [true/false] - If set to true everything accessible by this class will remain the same names, if set to false
     *    the only name left will be names of top hierarchy elements, everything else is going to get minified
     *    (so if class was passed only the classes name will remain the same, if set of functions in file only names of functions)
     *
     *  - single-file=true [true/false] - Minifier will try to put everything in one file for quicker load time which means all imports will
     *    be copied and put into main file, set to false to just leave every method/function where it was and just minified
     *
     *  - import-variant='default' ['default'/'omit'/'only'] - If single-file is set to false and you want to create semi-single-file you can
     *    decide here which imports should be included ('only' variant => `Tetraquark::ONLY`) or which should be skipped
     *    ('omit' variant => `Tetraquark::OMIT`) and rest will be imported.
     *    Default ('default' variant => `Tetraquark::DEFAULT`) is just default behaviour and default values.
     *
     *  - imports=[] - Here you can define which imports should be included (scripts checks by imports name so if you import { Func1, Func2 }
     *    then set it to ['Func1', 'Func2'] or if you onclude whole class you can define which methods you want to include ['Class.Func1']),
     *
     *  - minify-variant='default' ['default'/'omit'/'only'] - If you've set `only-nested` to false, you might want to still leave few
     *    methods/functions/attributes with their name for later usage. Variants are the same as for `import-variant`:
     *    ('only' variant => `Tetraquark::ONLY`) or which should be skipped ('omit' variant => `Tetraquark::OMIT`).
     *    Default ('default' variant => `Tetraquark::DEFAULT`) is just default behaviour and default values.
     *
     *  - exceptions=[] - Same as in `imports`, depending on what was chosen in `minify-variant` this variable should contains "path" to
     *    methods/functions/attributes you want to exclude from minifing or only include pointed resources as minified.
     * ]
     * @var array
     */
    private array $settings = [];
    private const DEFAULT = 'default';
    private const OMIT    = 'omit';
    private const ONLY    = 'only';
    public function __construct(array $settings = [])
    {
        $this->validateSettings($settings);
    }

    private function validateSettings(array $settings): void
    {
        $defaultSettings = [
            "only-nested" => [
                "options" => [true, false],
                "default" => true,
            ],
            "single-file" => [                                        // This means we will copy contents on imports into this file
                "options" => [true, false],
                "default" => true,
            ],
            "import-variant" => [
                "options" => [self::DEFAULT, self::OMIT, self::ONLY], // Possible: Default, Omit, Only
                "default" => self::DEFAULT,
            ],
            "imports" => [
                "options" => [],
                "default" => [],
            ],
            "minify-variant" => [
                "options" => [self::DEFAULT, self::OMIT, self::ONLY], // Possible: Default, Omit, Only
                "default" => self::DEFAULT,
            ],
            "exceptions" => [
                "options" => [],
                "default" => [],
            ],
        ];

        foreach ($defaultSettings as $key => $value) {
            if (
                ($settings[$key] ?? false)
                && \sizeof($value['options']) > 0                     // If empty any value works
                && !\in_array($value['options'], $settings[$key])
            ) {
                throw new TetraquarkException("Not allowed value in " . $key, 400);
            }

            $this->settings[$key] = $settings[$key] ?? $value['default'];
        }
    }

    public function minify(string $path): string
    {
        $file = $this->getFile($path);
        $map  = $this->mapJS($file);
        return '';
    }

    protected function getFile(string $path)
    {
        if (!\is_file($path)) {
            throw new TetraquarkException('Passed file not found, did you provide absolute path?', 404);
        }

        return \file_get_contents($path);
    }

    protected function mapJS(string $contents): array
    {
        $map  = [];
        $item = [];
        for ($i=0; $i < \strlen($contents); $i++) {
            $letter = $contents[$i];
            if ($letter == ' ') {
                $this->getWord($item, $contents, $i);
                continue;
            }
            if ($this->isEndChar($letter)) {
                $this->getWord($item, $contents, $i);
                if (\sizeof($item) > 0) {
                    $map[] = $item;
                    $item = [];
                }
            }
        }
        var_dump($map);
        return $map;
    }

    private function getWord(array &$item, string &$contents, int &$i): void
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
}
