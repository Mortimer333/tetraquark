<?php declare(strict_types=1);

namespace Tests\Integration;

use Tetraquark\{Reader, Exception};
use Content\Utf8 as Content;
use Tetraquark\Model\{LandmarkResolverModel, CustomMethodEssentialsModel, SettingsModel};

/**
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
class CheckReaderMapGenerationTest extends BaseIntegration
{
    public function testSimpleSetup(): void
    {
        $schemat = $this->getSchematPath('simple');
        $script = $this->getScriptPath('simple.js');

        $reader = new Reader($schemat);
        $map = $reader->getMap();
        $methods = $reader->getMethods();
        $this->assertEquals($this->getCompiled('simple/instruction'), $map);
        $this->assertEquals($this->getCompiled('simple/methods'), $methods);

        $analysis = $reader->read($script, true, displayBlocks: false);
        $analysisArray = json_decode(json_encode($analysis), true);
        $this->assertEquals($this->getCompiled('simple/analysis'), $analysisArray);
    }

    // /**
    //  * @uses Validate::isStringLandmark
    //  */
    // public function testMethodSetup(): void
    // {
    //
    // }
}
