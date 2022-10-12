<?php declare(strict_types=1);

namespace Tests\Unit\Analyzer\JavaScript\Util;

use Tetraquark\Analyzer\JavaScript\Util\Helper;
use Tetraquark\Model\CustomMethodEssentialsModel;
use Content\Utf8 as Content;

/**
 * @covers \Tetraquark\Analyzer\JavaScript\Util\Helper
 * @uses \Tetraquark\Model\BasePolymorphicModel
 */
class HelperTest extends BaseUtil
{
    /**
     * @covers \Tetraquark\Analyzer\JavaScript\Util\Helper::checkIfValidVarEnd
     * @uses \Tetraquark\Str::getPreviousLetter
     * @uses \Tetraquark\Str::getNextLetter
     * @uses \Tetraquark\Str::getPreviousWord
     * @uses \Tetraquark\Str::getNextWord
     * @uses \Tetraquark\Str::pascalize
     * @uses \Tetraquark\Str::utf8rev
     * @uses \Tetraquark\Analyzer\JavaScript\Validate::isOperator
     * @uses \Tetraquark\Validate::isStringLandmark
     * @uses \Tetraquark\Analyzer\JavaScript\Validate::isComment
     * @uses \Tetraquark\Analyzer\JavaScript\Validate::isExtendingKeyWord
     * @dataProvider provideVarEnds
     */
    public function testIVarEndIsProperlyValidated(string $content, int $start, int $i, bool $expected): void
    {
        $essentails = new CustomMethodEssentialsModel([
            "content" => new Content($content),
            "i"       => $i
        ]);
        $this->assertEquals($expected, Helper::checkIfValidVarEnd($essentails, $start));
    }

    public function provideVarEnds(): array
    {
        $complicated = "var name = (\n\tfunc(1, 2, 3) - var2\n) + 'text\nmore text';";
        return [
            ["var name = 1 + 2\nlet b = c;", 16, 10, true],
            ["var name = 1 + 2\n + c;", 16, 10, false],
            ["var name = 1 + 2 +\n c;", 18, 10, false],
            [$complicated, 12, 10, false],
            [$complicated, 34, 10, false],
            [$complicated, 55, 10, true],
            ["var abc = class instanceof\n ClassName;", 26, 9, false],
            ["var abc = class \ninstanceof ClassName;", 16, 9, false],
            ["var abc = a", 11, 9, true],
            ["var abc =\n 'a'", 12, 9, true],
        ];
    }

    /**
     * @covers \Tetraquark\Analyzer\JavaScript\Util\Helper::finishVarEnd
     * @uses \Tetraquark\Str::pascalize
     */
    public function testIfVarIsProperlyFinished(): void
    {
        $partToBeCut = " b + 3;";
        $endLetter = ';';
        $iBefore = 7;
        $iAfter = $iBefore + \mb_strlen($partToBeCut) - 1;
        $essentails = new CustomMethodEssentialsModel([
            "content" => new Content("var a =" . $partToBeCut),
            "i" => $iBefore,
            "data" => []
        ]);
        Helper::finishVarEnd($essentails, 13, $endLetter);
        $this->assertEquals(["var" => " b + 3;", "stop" => $endLetter], $essentails->getData());
        $this->assertEquals($iAfter, $essentails->getI());
    }

    /**
     * @covers \Tetraquark\Analyzer\JavaScript\Util\Helper::finishVarEnd
     * @uses \Tetraquark\Str::pascalize
     * @uses \Tetraquark\Str::getNextLetter
     * @uses \Tetraquark\Str::getNextWord
     * @uses \Content\Utf8::isWhitespace
     * @dataProvider provideNextChains
     */
    public function testIfNextChainIsFoundProperly(string $content, int $pos, array $methods, array $expectedData, int $expectedPos): void
    {
        $essentails = new CustomMethodEssentialsModel([
            "content" => new Content($content),
            "i" => 0,
            "data" => [],
            "methods" => $methods
        ]);
        $newPos = Helper::getNextChain($essentails, $pos);
        $this->assertEquals($expectedData, $essentails->getData());
        $this->assertEquals($expectedPos, $newPos);
    }

    public function provideNextChains(): array
    {
        return [
            ["chain", 4, [], [], 4],
            ["chain.chain.chain", 5, [], [], 16],
            ["chain . chain . chain", 5, [], [], 20],
            ["chain(argument).chain", 5, [
                "find" => fn ($essentials) => $essentials->setI(14),
            ], [], 20],
            ["chain ( argument ) .chain", 5, [
                "find" => fn ($essentials) => $essentials->setI(17),
            ], [], 24],
            ["chain(argument)(argument)", 5, [
                "find" => fn ($essentials) => $essentials->setI($essentials->getI() + 8),
            ], [], 24],
            ["chain.chain = abc;", 5, [
                "varend" => fn ($essentials) => $essentials->setI(17),
            ], [], 17],
        ];
    }
}
