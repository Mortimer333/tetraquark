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
 */
class JavaScriptAnalyzerTest extends BaseAnalyzer
{
    public function testIfAndShortIf(): void
    {
        $test = 'ifandshortif';
        $script = $this->getJsScriptPath($test);
        $reader = $this->getJsReader();
        $analysis = $reader->read($script, true, displayBlocks: false);
        $this->log(json_encode($analysis, JSON_PRETTY_PRINT));
        $this->assertEquals($this->getAnalysis($test), $analysis);
    }

    protected function getJsReader(): Reader
    {
        return new Reader(JavaScriptAnalyzerAbstract::class); // allow caching - will make tests finish quicker
    }

    protected function getJsScriptPath(string $name): string
    {
        $path = __DIR__ . '/JavaScript/script/' . ltrim($name, '/') . '.js';
        if (!is_file($path)) {
            throw new \Exception(sprintf('Script %s doesn\'t exist', $path));
        }

        return $path;
    }

    protected function getAnalysis(string $name): array
    {
        $path = __DIR__ . '/JavaScript/analysis/' . ltrim($name, '/') . '.json';
        if (!is_file($path)) {
            throw new \Exception(sprintf('Script %s doesn\'t exist', $path));
        }

        $json = json_decode(file_get_contents($path), true);
        if (is_null($json) || (!$json && !empty($json))) {
            throw new \Exception(sprintf('Analysis %s is malformed', $path));
        }
        return $json;
    }
}
