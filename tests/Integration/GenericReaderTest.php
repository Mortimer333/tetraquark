<?php declare(strict_types=1);

namespace Tests\Integration;

use Tetraquark\Reader;

/**
 * Those tests are check integration of all modules as they all were made for this class
 * @covers \Tetraquark\Reader
 * @covers \Tetraquark\Str
 * @covers \Tetraquark\Validate
 * @covers \Tetraquark\Model\BaseModel
 * @covers \Tetraquark\Model\BaseBlockModel
 * @covers \Tetraquark\Model\BasePolymorphicModel
 * @covers \Tetraquark\Model\LandmarkResolverModel
 * @covers \Tetraquark\Model\CustomMethodEssentialsModel
 * @covers \Tetraquark\Model\SettingsModel
 * @covers \Tetraquark\Model\Block\BlockModel
 * @covers \Tetraquark\Model\Block\ScriptBlockModel
 */
class GenericReaderTest extends BaseIntegration
{
    /**
     * @dataProvider provideSetups
     */
    public function testSetup(string $schemat, string $script, string $instruction, string $methodsPath, string $analysisPath): void
    {
        $script  = $this->getScriptPath($script);
        $reader  = $this->getReaderWithTestAnalyzer($schemat);
        $map     = $reader->getMap();
        $methods = $reader->getMethods();
        $this->assertEquals($this->getCompiled($instruction), $map);
        $this->assertEquals($this->getCompiled($methodsPath), $methods);

        $analysis = $reader->read($script, true, displayBlocks: false);
        $analysisArray = json_decode(json_encode($analysis), true);
        $this->assertEquals($this->getCompiled($analysisPath), $analysisArray);
    }

    public function provideSetups(): array
    {
        $prefix = 'generic/';
        $types = ["simple", "method", "extend", "settings", "comments", "block"];
        $cases = [];
        foreach ($types as $type) {
            $cases[] = [
                $prefix . $type,
                $prefix . $type,
                $prefix . $type . '/instruction',
                $prefix . $type. '/methods',
                $prefix . $type . '/analysis'
            ];
        }
        return $cases;
    }
}
