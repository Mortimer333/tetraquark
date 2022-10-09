<?php declare(strict_types=1);

namespace Tests\Integration\Analyzer\JavaScript;

use Tests\Integration\TestAnalyzerAbstract;
use Tetraquark\Reader;
use Tetraquark\Analyzer\JavaScript\Methods;
use Content\Utf8 as Content;
use Tetraquark\Model\CustomMethodEssentialsModel;

/**
 * @covers \Tetraquark\Analyzer\JavaScript\Methods
 * @uses Tetraquark\Model\BasePolymorphicModel
 * @uses Tetraquark\Str::pascalize
 */
class MethodsTest extends BaseJavaScript
{
    /**
     * @covers \Tetraquark\Analyzer\JavaScript\Methods::varEnd
     * @dataProvider provideVariables
     * @uses \Tetraquark\Reader
     * @uses \Tetraquark\Analyzer\JavaScript\Util\Helper
     * @uses \Tetraquark\Validate
     * @uses \Tetraquark\Analyzer\JavaScript\Validate
     * @uses \Tetraquark\Str
     */
    public function testFindProperEndOfVariableDefinition(
        array $schemat, string $content, int $pos, bool $comma, bool $eRes, int $ePos, array $eData
    ): void {
        $reader = $this->getReaderWithTestAnalyzer($schemat);
        $content = new Content($content);
        $essentials = new CustomMethodEssentialsModel([
            "content" => $content,
            "i" => $pos,
            "data" => [],
            "letter" => $content->getLetter($pos),
            "reader" => $reader
        ]);
        $res = Methods::varEnd($essentials, comma: $comma);
        $this->assertEquals($eRes, $res);
        $this->assertEquals($ePos, $essentials->getI());
        $this->assertEquals($eData, $essentials->getData());
    }

    public function provideVariables(): array
    {
        $schemat = [
            "comments" => [
                "/" => [
                    "/" => "\n"
                ]
            ],
            "remove" => [
                "comments" => true
            ]
        ];

        return [
            [$schemat, "", 0, true, true, 0, []],
            [$schemat, "var a = 2;", 7, true, true, 9, ["var" => " 2;", "stop" => ";"]],
            [
                $schemat,
                "var a = {name : 2, name2: { var2: 3}} + {asd};",
                7,
                true,
                true,
                44,
                ["var" => " {name : 2, name2: { var2: 3}} + {asd}", "stop" => "}"]
            ],
            [
                $schemat,
                "var a = a + \n b \n - 2 + function(asd, asd, ads);",
                7,
                true,
                true,
                46,
                ["var" => " a + \n b \n - 2 + function(asd, asd, ads)", "stop" => ")"]
            ],
        ];
    }
}
