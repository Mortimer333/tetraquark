<?php declare(strict_types=1);

namespace Tests\Unit;

use Tetraquark\{Reader, Exception};
use Content\Utf8 as Content;
use Tetraquark\Model\{LandmarkResolverModel, CustomMethodEssentialsModel, SettingsModel};

class ReaderTest extends BaseUnit
{
    public function testSimpleSetup(): void
    {
        $schemat = $this->getSchemat('JS/simple');
        $script = $this->getScript('JS/simple.js');
    }
}
