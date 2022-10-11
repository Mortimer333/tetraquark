<?php declare(strict_types=1);

namespace Tests\Unit\Analyzer\JavaScript;

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
     * @covers \Tetraquark\Analyzer\JavaScript\Methods::getDefinitions
     */
    public function testMethodsExistInClass(): void
    {
        $methods = Methods::getDefinitions();
        foreach ($methods as $key => $realName) {
            $this->assertEquals(true, method_exists(Methods::class, $realName));
        }
    }

    /**
     * @covers \Tetraquark\Analyzer\JavaScript\Methods::get
     */
    public function testMethodsAreInClosures(): void
    {
        $methods = Methods::get();
        foreach ($methods as $key => $closure) {
            $this->assertEquals(true, $closure instanceof \Closure);
        }
    }

    /**
     * @covers \Tetraquark\Analyzer\JavaScript\Methods::consecutiveCaller
     * @uses Tetraquark\Str::getPreviousLetter
     * @dataProvider provideConsecutiveCallers
     */
    public function testConsecutiveCallerValidatesProperly(string $content, int $pos, bool $expected): void
    {
        $essentials = new CustomMethodEssentialsModel([
            "content" => new Content($content),
            "i" => $pos,
        ]);
        $this->assertEquals($expected, Methods::consecutiveCaller($essentials));
    }

    public function provideConsecutiveCallers(): array
    {
        return [
            ["content", 0, false],
            ["content", 5, false],
            [")content)", 7, false],
            [")content)", 100, false],
            [")content)", -2, false],
            ["(args)(args)", 7, true],
            ["(args) (args)", 8, true],
        ];
    }

    /**
     * @covers \Tetraquark\Analyzer\JavaScript\Methods::this
     * @dataProvider provideThisCases
     */
    public function testMethodProperlyValidatesThis(string $content, int $pos, bool $eRes): void
    {
        $essentials = new CustomMethodEssentialsModel([
            "content" => new Content($content),
            "i" => $pos,
        ]);
        $this->assertEquals($eRes, Methods::this($essentials));
    }

    public function provideThisCases(): array
    {
        return [
            ["nothing", 0, false],
            ["nothing", -1, false],
            ["nothing", 100, false],
            ["", 2, false],
            ["here: thisafter", 6, true],
            ["here: this", 6, true],
        ];
    }

    /**
     * @covers \Tetraquark\Analyzer\JavaScript\Methods::number
     * @dataProvider provideNumberCases
     */
    public function testValidatesNumberProperly(string $content, int $pos, bool $eRes, array $eData, int $eI): void
    {
        $content = new Content($content);
        $essentials = new CustomMethodEssentialsModel([
            "content" => $content,
            "i" => $pos,
            "letter" => $content->getLetter($pos),
            "data" => []
        ]);
        $this->assertEquals($eRes, Methods::number($essentials));
        $this->assertEquals($eData, $essentials->getData());
        $this->assertEquals($eI, $essentials->getI());
    }

    public function provideNumberCases(): array
    {
        return [
            ["nothing", 0, false, [], 0],
            ["nothing", -1, false, [], -1],
            ["nothing", 100, false, [], 100],
            ["", 2, false, [], 2],
            ["here: 2", 6, true, ["number" => "2"], 6],
            ["here: 2.2", 6, true, ["number" => "2.2"], 8],
            ["here: 2.", 6, false, [], 6],
            ["here: 2.2 asd", 6, true, ["number" => "2.2"], 8],
        ];
    }

    /**
     * @covers \Tetraquark\Analyzer\JavaScript\Methods::templateLiteral
     */
    public function testTemplateLiterarThrowExceptionIfDataIsNotFound(): void
    {
        $essentials = new CustomMethodEssentialsModel([
            "data" => []
        ]);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Couldn't find template literal in data with name:");
        Methods::templateLiteral($essentials);
    }

    /**
     * @covers \Tetraquark\Analyzer\JavaScript\Methods::templateLiteral
     * @dataProvider provideTemplateLiterals
     * @uses Tetraquark\Str
     * @uses Tetraquark\Validate
     */
    public function testTemplateLiteralIsProperlyTransformed(string $template, array $eData): void
    {
        $essentials = new CustomMethodEssentialsModel([
            "data" => [
                "template" => $template,
            ]
        ]);
        Methods::templateLiteral($essentials);
        $this->assertEquals($eData, $essentials->getData());
    }

    public function provideTemplateLiterals(): array
    {
        return [
            ["", ["template" => '""']],
            ["str", ["template" => '"str"']],
            ['str${var}str', ["template" => '"str" var "str"']],
            ['str${var}str${var2}', ["template" => '"str" var "str" var2 ""']],
            ['${var2}str${var}str', ["template" => '"" var2 "str" var "str"']],
            [
                '${(function() {return `string in function in string`})()}str${var}str',
                ["template" => '"" (function() {return `string in function in string`})() "str" var "str"']
            ],
            ['with "double" quotes', ["template" => '"with \"double\" quotes"']],
            ["template 'with' single", ["template" => '"template \'with\' single"']]
        ];
    }

    /**
     * @TODO Maybe integration would be better?
     * depends testEndIfProperlyFoundForVars
     * @covers \Tetraquark\Analyzer\JavaScript\Methods::objectEnd
     * @uses \Tetraquark\Reader::getDefaultMethods
     * @uses \Tetraquark\Str::skip
     * @uses \Tetraquark\Str::skipBlock
     * @uses \Tetraquark\Validate::isStringLandmark
     * @uses \Tetraquark\Validate::isTemplateLiteralLandmark
     * @dataProvider provideObjectEnds
     */
    public function testEndOfObjectItemIsProperlyFound(string $content, int $pos, array $methods, array $eData, int $ePos): void
    {
        $content = new Content($content);
        $essentials = new CustomMethodEssentialsModel([
            "content" => $content,
            "methods" => $methods,
            "i" => $pos,
            "data" => [],
            "letter" => $content->getLetter($pos)
        ]);
        Methods::objectEnd($essentials);
        $this->assertEquals($eData, $essentials->getData());
        $this->assertEquals($ePos, $essentials->getI());
    }

    public function provideObjectEnds(): array
    {
        $reader = new Reader();
        $methods = [
            "varend" => fn (...$args) => Methods::varend(...$args),
            "find" => fn (...$args) => $reader->getDefaultMethods()['find'](...$args),
        ];
        return [
            ["", 0, $methods, [], 0],
            ["{name: 2}", 6, $methods, [], 7],
            ["{name: { a: v } }", 6, $methods, [], 15],
            ["{name: function (a,b,c){ var a, b,c; } }", 6, $methods, [], 38],
            ["{name: 2, name2: c}", 6, $methods, [], 7],
            ["{name: 2 /* }, */, name2: c}", 6, $methods, [], 16],
        ];
    }

    /**
     * @covers \Tetraquark\Analyzer\JavaScript\Methods::symbol
     * @uses \Tetraquark\Validate::isStringLandmark
     * @uses \Content\Utf8::isWhitespace
     * @dataProvider provideSymbols
     */
    public function testSymbolValidating(string $content, int $pos, array $eData, bool $eRes): void
    {
        $content = new Content($content);
        $essentials = new CustomMethodEssentialsModel([
            "content" => $content,
            "i" => $pos,
            "data" => [],
            "letter" => $content->getLetter($pos)
        ]);
        $res = Methods::symbol($essentials);
        $this->assertEquals($eData, $essentials->getData());
        $this->assertEquals($eRes, $res);
    }

    public function provideSymbols(): array
    {
        return [
            ["", 0, [], false],
            ["code", 2, [], false],
            ["code", 5, [], false],
            ["1", 0, [], false],
            [" ", 0, [], false],
            ["$", 0, ["symbol" => "$"], true],
            ["aa@", 2, ["symbol" => "@"], true],
        ];
    }

    /**
     * @covers \Tetraquark\Analyzer\JavaScript\Methods::assignment
     * @dataProvider provideAssigments
     */
    public function testFindsAssignmentProperly(string $content, int $pos, array $eData, int $ePos, bool $eRes): void
    {
        $content = new Content($content);
        $essentials = new CustomMethodEssentialsModel([
            "content" => $content,
            "i" => $pos,
            "data" => [],
            "letter" => $content->getLetter($pos)
        ]);
        $res = Methods::assignment($essentials);
        $this->assertEquals($eRes, $res);
        $this->assertEquals($ePos, $essentials->getI());
        $this->assertEquals($eData, $essentials->getData());
    }

    public function provideAssigments(): array
    {
        return [
            ["", 0, [], 0, false],
            ["code", 2, [], 2, false],
            ["code", 5, [], 5, false],
            ["1", 0, [], 0, false],
            [" ", 0, [], 0, false],
            ["+", 0, ["assignment" => "+"], 0, true],
            ["++", 0, ["assignment" => "+"], 0, true],
            [">>", 0, ["assignment" => ">>"], 1, true],
            [">>>", 0, ["assignment" => ">>>"], 2, true],
        ];
    }

    /**
     * @covers \Tetraquark\Analyzer\JavaScript\Methods::word
     * @uses \Tetraquark\Analyzer\JavaScript\Validate::isJSValidVariable
     * @uses \Tetraquark\Str::getNextWord
     * @dataProvider provideWords
     */
    public function testWordIsValidatedProperly(string $content, int $pos, bool $varValidation, array $eData, int $ePos, bool $eRes): void
    {
        $content = new Content($content);
        $essentials = new CustomMethodEssentialsModel([
            "content" => $content,
            "i" => $pos,
            "data" => [],
            "letter" => $content->getLetter($pos),
        ]);
        $res = Methods::word($essentials, varValidation: $varValidation);
        $this->assertEquals($eRes, $res);
        $this->assertEquals($ePos, $essentials->getI());
        $this->assertEquals($eData, $essentials->getData());
    }

    public function provideWords(): array
    {
        return [
            ["", 0, false, [], 0, false],
            ["", 10, false, [], 10, false],
            ["asd", 10, false, [], 10, false],
            ["", -4, false, [], -4, false],
            ["asd", -4, false, [], -4, false],
            ["word word2 word3", 5, true, ["word" => "word2"], 9, true],
            ["word 2word word3", 5, true, [], 5, false],
            ["word var word3", 5, true, [], 5, false],
            ["word var word3", 5, false, ["word" => "var"], 7, true],
        ];
    }
}
