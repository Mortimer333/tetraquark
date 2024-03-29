<?php declare(strict_types=1);

namespace Tests\Integration\Analyzer;

use Tetraquark\Reader;
use Tetraquark\Analyzer\JavaScript\Methods;
use Tetraquark\Analyzer\JavaScriptAnalyzerAbstract;
use Content\Utf8 as Content;
use Tetraquark\Model\CustomMethodEssentialsModel;

/**
 * @covers \Tetraquark\Reader
 * @covers \Tetraquark\Str
 * @covers \Tetraquark\Validate
 * @covers \Tetraquark\Analyzer\JavaScriptAnalyzerAbstract
 * @covers \Tetraquark\Analyzer\JavaScript\Instruction
 * @covers \Tetraquark\Analyzer\JavaScript\Methods
 * @covers \Tetraquark\Analyzer\JavaScript\Validate
 * @covers \Tetraquark\Analyzer\JavaScript\Util\Helper
 * @covers \Tetraquark\Analyzer\JavaScript\Util\LandmarkStorage
 * @covers \Tetraquark\Model\BaseModel
 * @covers \Tetraquark\Model\BaseBlockModel
 * @covers \Tetraquark\Model\BasePolymorphicModel
 * @covers \Tetraquark\Model\LandmarkResolverModel
 * @covers \Tetraquark\Model\CustomMethodEssentialsModel
 * @covers \Tetraquark\Model\SettingsModel
 * @covers \Tetraquark\Model\Block\BlockModel
 * @covers \Tetraquark\Model\Block\ScriptBlockModel
 * @covers \Tetraquark\Factory\ClosureFactory
 * @covers \Tetraquark\Factory\ClosureFactory
 */
class JavaScriptAnalyzerTest extends BaseAnalyzer
{
    public function testJavaScriptSettings(): void
    {
        $settings = [
            "comments" => [
                "/" => [
                    "/" => "\n",
                    "*" => "*/"
                ],
            ],
            "prepare" => [
                "content" => fn() => null,
                "missed" => fn() => null,
            ],
            "shared" => [
                "ends" => [
                    "\n" => true,
                    ";" => true,
                    "}" => true,
                    "," => true,
                    ")" => true,
                ],
            ],
            "remove" => [
                "comments" => false,
                "additional" => false,
            ],
        ];

        $reader = $this->getJsReader();
        $schema = $reader->getSchema() ?? []; // loosing reference by null coalescening operator
        $settings['instructions'] = $schema['instructions'];
        $settings['methods'] = $schema['methods'];
        $this->assertEquals($schema, $settings);
    }

    /**
     * @dataProvider provideScripts
     */
    public function testScripts(string $name, Reader $reader, bool $save = false): void
    {
        $script = $this->getJsScriptPath($name);
        $analysis = $reader->read($script, true, displayBlocks: false);
        if ($save) {
            $path = $this->getAnalysisPath($name, false);
            $file = fopen($path, 'w');
            fwrite($file, json_encode($analysis, JSON_PRETTY_PRINT));
            fclose($file);
        }
        $this->assertEquals($this->getAnalysis($name), json_decode(json_encode($analysis), true));
    }

    public function provideScripts(): array
    {
        $reader = $this->getJsReader();
        return [
            "true false" => ['truefalse', $reader],
            "string" => ['string', $reader],
            "if and short if" => ['ifandshortif', $reader],
            "class definition" => ['classdefinition', $reader],
            "comma" => ['comma', $reader],
            "keyword" => ['keyword', $reader],
            "this" => ['this', $reader],
            "equal" => ['equal', $reader],
            "unequal" => ['unequal', $reader],
            "spread variable" => ['spreadvariable', $reader],
            "array" => ['array', $reader],
            "spread array" => ['spreadarray', $reader],
            "new instance" => ['newinstance', $reader],
            "symbol" => ['symbol', $reader],
            "yield" => ['yield', $reader],
            "variable definition" => ['variabledefinition', $reader],
            "scope" => ['scope', $reader],
            "number" => ['number', $reader],
            "staticvar" => ['staticvar', $reader],
            "arrow method (async)" => ['arrowmethod', $reader],
            "array deconstruct" => ['arraydeconstruct', $reader],
            "method, constant method and caller" => ['methodconstantmethodandcaller', $reader],
            "consecutive caller" => ['consecutive', $reader],
            "get set async with static" => ['getsetasyncstatic', $reader],
            "try catch finally" => ['trycatchfinally', $reader],
            "chain" => ['chain', $reader],
            "do while" => ['dowhile', $reader],
            "while and short while" => ['whileandshortwhile', $reader],
            "while and short while" => ['elseifelse', $reader],
            "for and short for" => ['forandshortfor', $reader],
            "for in for of" => ['forinforof', $reader],
            "function and generator" => ['functionandgenerator', $reader],
            "object and spread object" => ['objectandspreadobject', $reader],
            "return" => ['return', $reader],
            "switch and cases" => ['switchandcases', $reader],
            "import" => ['import', $reader],
            "export" => ['export', $reader],
        ];
    }

    protected function getJsReader(): Reader
    {
        // allow caching - will make tests finish quicker
        return (new Reader(JavaScriptAnalyzerAbstract::class))->setFailsave(true);
    }

    protected function getJsScriptPath(string $name, bool $check = true): string
    {
        $path = __DIR__ . '/JavaScript/script/' . ltrim($name, '/') . '.js';
        if ($check && !is_file($path)) {
            throw new \Exception(sprintf('Script %s doesn\'t exist', $path));
        }

        return $path;
    }

    protected function getAnalysisPath(string $name, bool $check = true): string
    {
        $path = __DIR__ . '/JavaScript/analysis/' . ltrim($name, '/') . '.json';
        if ($check && !is_file($path)) {
            throw new \Exception(sprintf('Script %s doesn\'t exist', $path));
        }
        return $path;
    }

    protected function getAnalysis(string $name): array
    {
        $path = $this->getAnalysisPath($name);
        $json = json_decode(file_get_contents($path), true);
        if (is_null($json) || (!$json && !empty($json))) {
            throw new \Exception(sprintf('Analysis %s is malformed', $path));
        }
        return $json;
    }
}
