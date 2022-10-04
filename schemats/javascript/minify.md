# Plan how to minify JS

## Import
```js
let Ī = {};
Ī.y = Ī => {
    // Imported script
};
```

All exported scripts we will put inside anonymous method assigned to `Ī` import object. That way we can still run code with proper scope and not overflow global scope with variables just like proper import.

Import class:
```php
<?php declare(strict_types=1);

namespace Tetraquark;

use \Tetraquark\Block;
use \Tetraquark\Foundation\{BlockAbstract};

/**
 *  Class contains Imports in form of the string
 */
class Import
{
    protected array $scripts   = [];
    protected array $retrivals = [];
    protected string $lastAlias = '';

    public function __construct()
    {
        // nothing
    }

    public function setScript(string $path, string $script): self
    {
        $this->scripts[$path] = [
            "script" => $script,
            "alias"  => BlockAbstract::generateAliasStatic($this->lastAlias),
            "retrivals" => []
        ];
        $this->lastAlias = $this->scripts[$path]['alias'];
        return $this;
    }

    public function scriptExists(string $path): bool
    {
        return isset($this->scripts[$path]);
    }


    public function addRetrival(string $path, string $importsFrom, string $retrival): self
    {
        if (!isset($this->retrivals[$path])) {
            $this->retrivals[$path] = [];
        }
        $this->retrivals[$path][] = [
            "retrival" => $retrival,
            "import" => $importsFrom
        ];
        return $this;
    }

    public function getRetrivals(): array
    {
        return $this->retrivals;
    }

    public function getScripts(): array
    {
        return $this->scripts;
    }

    public function getScript(string $path): array
    {
        return $this->scripts[$path] ?? throw new Exception('Import not found with path ' . htmlentities($path), 404);
    }

    public function recreate(): string
    {
        if (\sizeof($this->scripts) == 0) {
            return '';
        }

        $imports = 'let Ī={};';
        $skipScripts = [];
        $addToTheEndImports = '';
        foreach ($this->getRetrivals() as $path => $retrivals) {
            if ($this->scriptExists($path)) {
                $script = $this->getScript($path);
                $imports .= 'Ī.' . $script['alias'] . '=' . 'Ī=>{';
                $imports .= $this->recreateRetrivals($retrivals);
                $imports .= $script['script'] . "}";
                $skipScripts[$path] = true;
            } else {
                $addToTheEndImports .= $this->recreateRetrivals($retrivals);
            }
        }

        foreach ($this->getScripts() as $path => $script) {
            if (isset($skipScripts[$path])) {
                continue;
            }
            $imports .= 'Ī.' . $script['alias'] . '=' . 'Ī=>{' . $script['script'] . "}";
        }

        $imports .= $addToTheEndImports . 'Ī=undefined;';

        return $imports;
    }

    private function recreateRetrivals(array $retrivals): string
    {
        $retrs = '';
        foreach ($retrivals as $retrival) {
            $alias = $this->getScript($retrival['import'])['alias'];
            $retrs .= $retrival['retrival'] . 'Ī.' . $alias . '(Ī);';
        }
        return $retrs;
    }
}
?>
```

## Export
Export stays like it is except if we are including imported script, then it has to be removed.
! important - figured out how to handle export from other files - `export * from "module-name";`.

## Alias table:
```php
protected static array $aliasMap = [
    'df' => 'a', 'a' => 'b', 'b' => 'c', 'c' => 'd', 'd' => 'e', 'e' => 'f', 'f' => 'g', 'g' => 'h', 'h' => 'i', 'i' => 'j', 'j' => 'k',
    'k' => 'l', 'l' => 'm', 'm' => 'n', 'n' => 'o', 'o' => 'p', 'p' => 'r', 'r' => 's', 's' => 't', 't' => 'u', 'u' => 'w', 'w' => 'z',
    'z' => 'y', 'y' => 'x', 'x' => 'q', 'q' => 'v', 'v' => 'µ', 'µ' => 'ß', 'ß' => 'à', 'à' => 'á', 'á' => 'â', 'â' => 'ã', 'ã' => 'ä',
    'ä' => 'å', 'å' => 'æ', 'æ' => 'ç', 'ç' => 'è', 'è' => 'é', 'é' => 'ê', 'ê' => 'ë', 'ë' => 'ì', 'ì' => 'í', 'í' => 'î', 'î' => 'ï',
    'ï' => 'ð', 'ð' => 'ñ', 'ñ' => 'ò', 'ò' => 'ó', 'ó' => 'ô', 'ô' => 'õ', 'õ' => 'ö', 'ö' => 'ø', 'ø' => 'ù', 'ù' => 'ú', 'ú' => 'û',
    'û' => 'ü', 'ü' => 'ý', 'ý' => 'þ', 'þ' => 'ÿ', 'ÿ' => 'ā', 'ā' => 'ă', 'ă' => 'ą', 'ą' => 'ć', 'ć' => 'ĉ', 'ĉ' => 'ċ', 'ċ' => 'č',
    'č' => 'ď', 'ď' => 'đ', 'đ' => 'ē', 'ē' => 'ĕ', 'ĕ' => 'ė', 'ė' => 'ę', 'ę' => 'ě', 'ě' => 'ĝ', 'ĝ' => 'ğ', 'ğ' => 'ġ', 'ġ' => 'ģ',
    'ģ' => 'ĥ', 'ĥ' => 'A', 'A' => 'B', 'B' => 'C', 'C' => 'D', 'D' => 'E', 'E' => 'F', 'F' => 'G', 'G' => 'H', 'H' => 'I', 'I' => 'J',
    'J' => 'K', 'K' => 'L', 'L' => 'M', 'M' => 'N', 'N' => 'O', 'O' => 'P', 'P' => 'Q', 'Q' => 'R', 'R' => 'S', 'S' => 'T', 'T' => 'U',
    'U' => 'V', 'V' => 'W', 'W' => 'X', 'X' => 'Y', 'Y' => 'Z', 'Z' => 'À', 'À' => 'Á', 'Á' => 'Â', 'Â' => 'Ã', 'Ã' => 'Ä', 'Ä' => 'Å',
    'Å' => 'Æ', 'Æ' => 'Ç', 'Ç' => 'È', 'È' => 'É', 'É' => 'Ê', 'Ê' => 'Ë', 'Ë' => 'Ì', 'Ì' => 'Í', 'Í' => 'Î', 'Î' => 'Ï', 'Ï' => 'Ð',
    'Ð' => 'Ñ', 'Ñ' => 'Ò', 'Ò' => 'Ó', 'Ó' => 'Ô', 'Ô' => 'Õ', 'Õ' => 'Ö', 'Ö' => 'Ø', 'Ø' => 'Ù', 'Ù' => 'Ú', 'Ú' => 'Û', 'Û' => 'Ü',
    'Ü' => 'Ý', 'Ý' => 'Þ', 'Þ' => 'Ā', 'Ā' => 'Ă', 'Ă' => 'Ą', 'Ą' => 'Ć', 'Ć' => 'Ĉ', 'Ĉ' => 'Ċ', 'Ċ' => 'Č', 'Č' => 'Ď', 'Ď' => 'Đ',
    'Đ' => 'Ē', 'Ē' => 'Ĕ', 'Ĕ' => 'Ė', 'Ė' => 'Ę', 'Ę' => 'Ě', 'Ě' => 'Ĝ', 'Ĝ' => 'Ğ', 'Ğ' => 'Ġ', 'Ġ' => 'Ģ', 'Ģ' => 'Ĥ', 'Ĥ' => 'Ħ',
    'Ħ' => 'Ĩ', 'Ĩ' => 'Ī', 'Ī' => '$', '$' => '_', '_' => false
];
```

## Options

```php
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

public const DEFAULT = 'default';
public const OMIT    = 'omit';
public const ONLY    = 'only';

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
```
