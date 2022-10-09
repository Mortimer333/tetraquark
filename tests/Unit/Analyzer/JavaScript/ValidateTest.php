<?php declare(strict_types=1);

namespace Tests\Unit\Analyzer\JavaScript;

use Tetraquark\Analyzer\JavaScript\Validate;
use Content\Utf8 as Content;

/**
 * @covers \Tetraquark\Analyzer\JavaScript\Validate
 */
class ValidateTest extends BaseJavaScript
{
    /**
     * @dataProvider provideVariablesToValidate
     * @covers \Tetraquark\Analyzer\JavaScript\Validate::isJSTakenKeyWord
     */
    public function testJavaScriptVariableAreProperlyValidated(string $variable, bool $expected): void
    {
        $this->assertEquals($expected, Validate::isJSValidVariable($variable));
    }

    public function provideVariablesToValidate(): array
    {
        return [
            ['normalVar', true],
            ['2StartsWithNumber', false],
            ['Has@symbol', false],
            ['_', true],
            ['var', false],
            ['var$', true],
        ];
    }

    /**
     * @dataProvider provideSymbolToValidate
     * @covers \Tetraquark\Analyzer\JavaScript\Validate::isSymbol
     */
    public function testJavaScriptSymbolsAreProperlyRecognized(string $sybmol, bool $expected): void
    {
        $this->assertEquals($expected, Validate::isSymbol($sybmol));
    }

    public function provideSymbolToValidate(): array
    {
        return [
            ['#', true],
            ['2', false],
            ['a', false],
            ['a#', false],
            [' ', false],
            ['_', true],
            ['-', true],
            ['@', true],
        ];
    }

    /**
     * @dataProvider provideOperators
     * @covers \Tetraquark\Analyzer\JavaScript\Validate::isOperator
     */
    public function testOperatorIsFoundProperly(string $letter, bool $isNextLine, bool $expected): void
    {
        $this->assertEquals($expected, Validate::isOperator($letter, $isNextLine));
    }

    public function provideOperators(): array
    {
        return [
            ["{", false, true],
            [")", true, true],
            [")", false, false],
        ];
    }

    /**
     * @dataProvider provideComments
     * @covers \Tetraquark\Analyzer\JavaScript\Validate::isComment
     */
    public function testCommentIsFoundProperly(int $start, string $content, bool $expected): void
    {
        $content = new Content($content);
        $this->assertEquals($expected, Validate::isComment($start, $content));
    }

    public function provideComments(): array
    {
        $comment = " // comment \n/*\n multi\n */";
        return [
            [0, $comment, false],
            [1, $comment, true],
            [13, $comment, true],
        ];
    }
}
