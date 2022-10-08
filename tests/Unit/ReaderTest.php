<?php declare(strict_types=1);

namespace Tests\Unit;

use Tetraquark\{Reader, Exception};
use Content\Utf8 as Content;
use Tetraquark\Model\{LandmarkResolverModel, CustomMethodEssentialsModel, SettingsModel};

/**
 * @covers \Tetraquark\Reader
 * @uses \Tetraquark\Model\LandmarkResolverModel
 * @uses \Tetraquark\Model\CustomMethodEssentialsModel
 * @uses \Tetraquark\Model\SettingsModel
 */
class ReaderTest extends BaseUnit
{
    /**
     * @covers \Tetraquark\Reader::schemaSetDefaults
     */
    public function testSettingDefaultsInSchemat(): void
    {
        $defaults = [
            "test1" => false,
            "test2" => [
                "test1" => true,
                "test2" => [
                    "test1" => "name"
                ]
            ],
            "test3" => 123,
        ];

        $custom = [
            "test1" => true,
            "test2" => [
                "test1" => false
            ],
        ];

        $expectedResult = [
            "test1" => true,
            "test2" => [
                "test1" => false,
                "test2" => [
                    "test1" => "name"
                ]
            ],
            "test3" => 123,
        ];

        $reader = new Reader();
        $schema = $reader->schemaSetDefaults($custom, $defaults);
        $this->assertEquals($expectedResult, $schema);
    }

    /**
     * @covers \Tetraquark\Reader::updateFromEssentials
     * @uses \Tetraquark\Model\BasePolymorphicModel
     * @uses \Tetraquark\Str::pascalize
     */
    public function testResolverGetsProperlyUpdatedWithEssentials(): void
    {
        $beforeMethods  = true;
        $beforePrevious = true;
        $beforeLmStart  = true;
        $beforeReader   = true;
        $landmark = new LandmarkResolverModel([
            "methods"  => $beforeMethods,
            "previous" => true,
            "lmStart"  => true,
            "reader"   => true,
            "data" => [],
            "name" => "Before"
        ]);
        $afterData = ["after" => 1];
        $afterName = "after";
        $essentials = new CustomMethodEssentialsModel([
            "methods"  => false,
            "previous" => false,
            "lmStart"  => false,
            "reader"   => false,
            "data" => $afterData,
            "name" => $afterName,
        ]);

        $reader = new Reader();
        $reader->updateFromEssentials($landmark, $essentials);
        $this->assertEquals($beforeMethods, $landmark->getMethods());
        $this->assertEquals($beforePrevious, $landmark->getPrevious());
        $this->assertEquals($beforeLmStart, $landmark->getLmStart());
        $this->assertEquals($beforeReader, $landmark->getReader());
        $this->assertEquals($afterData, $landmark->getData());
        $this->assertEquals($afterName, $landmark->getName());
    }

    /**
     * @covers \Tetraquark\Reader::resolveSettings
     * @uses \Tetraquark\Model\BasePolymorphicModel
     * @uses \Tetraquark\Str::pascalize
     */
    public function testSkipFromSettingsIsThrownWhenLandmarkIsSkipable(): void
    {
        $settings = new SettingsModel(0);
        $landmark = new LandmarkResolverModel([
            "landmark" => [
                Reader::FLAG_SKIP => true
            ],
            "settings" => $settings,
        ]);

        $reader = new Reader();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(Reader::SKIP);
        $reader->resolveSettings($landmark);
    }

    /**
     * @covers \Tetraquark\Reader::resolveSettings
     * @uses \Tetraquark\Model\BasePolymorphicModel
     * @uses \Tetraquark\Str::pascalize
     */
    public function testSkipFromSettingsIsThrownWhenSkipIsHigerThenZero(): void
    {
        $settings = new SettingsModel(1);
        $landmark = new LandmarkResolverModel([
            "landmark" => [],
            "settings" => $settings,
        ]);

        $reader = new Reader();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(Reader::SKIP);
        $reader->resolveSettings($landmark);
    }

    /**
     * @covers \Tetraquark\Reader::clearLandmark
     */
    public function testLandmarkIsCleared(): void
    {
        $dirty = [
            Reader::FLAG_SKIP => true,
            "data" => "vars",
            "custom" => [
                "_stop" => true,
            ],
            "_missed" => "asd"
        ];
        $excepted = [
            "data" => "vars",
            "_missed" => "asd",
        ];
        $reader = new Reader();
        $clean = $reader->clearLandmark($dirty);
        $this->assertEquals($excepted, $clean);
    }

    /**
     * @dataProvider providerClosestMatch
     * @covers \Tetraquark\Reader::findClosestMatch
     */
    public function testClosestMatchIsProperlyFound(string $needle, string $content, int $start, bool | int $excepted): void
    {
        $reader = new Reader();
        $pos = $reader->findClosestMatch($needle, new Content($content), $start);
        $this->assertEquals($excepted, $pos);
    }

    public function providerClosestMatch(): array
    {
        $comment = "// other comment */ more of the comment \n/* comment */ var code = 1;";
        return [
            ['*/', $comment, 43, 53],
            ["\n", $comment, 2, 40],
            ["comment", $comment, 0, 15],
        ];
    }

    /**
     * @covers \Tetraquark\Reader::encloseCustomData
     */
    public function testRemovesFlagsProperlyFromLandmark(): void
    {
        $landmark = [
            "_block" => [],
            "class" => "class",
            "customStuff" => true,
            "_extend" => [],
        ];
        $excepted = [
            "_custom" => [
                "class" => "class",
                "customStuff" => true,
            ],
            "_extend" => [],
            "_block" => [],
        ];
        $reader = new Reader();
        $landmark = $reader->encloseCustomData($landmark);
        $this->assertEquals($excepted, $landmark);
    }

    /**
     * @dataProvider provideMapsToMerge
     * @covers \Tetraquark\Reader::mergeMaps
     */
    public function testMapsAreMergedProperly(array $map, array $merged, array $excepted): void
    {
        $reader = new Reader();
        $merged = $reader->mergeMaps($map, $merged);
        $this->assertEquals($excepted, $merged);
    }

    public function provideMapsToMerge(): array
    {
        return [
            [
                json_decode('[{"a":{"b":{"c":{"_custom":{"class":1},"_stop":true}}}},{"a":{"b":{"e":{"_custom":{"class":2},"_stop":true}}}}]', true),
                [],
                json_decode('{"a":{"b":{"c":{"_custom":{"class":1},"_stop":true},"e":{"_custom":{"class":2},"_stop":true}}}}', true),
            ],
            [
                json_decode('[{"a":{"b":{"c":{"_custom":{"class":1},"_stop":true}}}},{"a":{"b":{"e":{"_custom":{"class":2},"_stop":true}}}},{"a":{"c":{"b":{"e":{"_custom":{"class":3},"_stop":true}}}}},{"a":{"c":{"b":{"d":{"_custom":{"class":4},"_stop":true}}}}}]', true),
                [],
                json_decode('{"a":{"c":{"b":{"d":{"_custom":{"class":4},"_stop":true},"e":{"_custom":{"class":3},"_stop":true}}},"b":{"e":{"_custom":{"class":2},"_stop":true},"c":{"_custom":{"class":1},"_stop":true}}}}', true)
            ]
        ];
    }

    /**
     * @dataProvider provideWellCases
     * @covers \Tetraquark\Reader::createWell
     */
    public function testWellCreation(array|string $well, array $excepted): void
    {
        $reader = new Reader();
        $well = $reader->createWell($well);
        $this->assertEquals($excepted, $well);
    }

    public function provideWellCases(): array
    {
        return [
            [
                ["firstStep", "secondStep", "thridStep"],
                ["firstStep" => ["secondStep" => ["thridStep" => []]]]
            ],
            [
                "well",
                ["w" => ["e" => ["l" => ["l" => []]]]]
            ],
            [[], []],
            ["", []]
        ];
    }

    /**
     * @covers \Tetraquark\Reader::getDefaultMethods
     */
    public function testRetrivalDfeaultMethods(): void
    {
        $reader = new Reader();
        $defaultMethods = $reader->getDefaultMethods();
        foreach ($defaultMethods as $key => $value) {
            $this->assertEquals(true, ctype_alpha($key));
            $this->assertEquals(true, is_callable($value));
        }
    }
}
