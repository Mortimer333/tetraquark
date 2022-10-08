<?php declare(strict_types=1);

namespace Tests\Unit;

use Tetraquark\Str;
use Content\Utf8 as Content;

/**
 * @covers \Tetraquark\Str
 * @uses \Tetraquark\Validate::isStringLandmark
 * @uses \Tetraquark\Validate::isTemplateLiteralLandmark
 * @uses \Tetraquark\Validate::isSpecial
 */
class StrTest extends BaseUnit
{
    protected array $skipTestBlock = [
        "normal"                         => '[code] { ...something... } [more code]',
        "nested"                         => '[code] { { ...nested... } } [more code]',
        "multi"                          => '[code] ${ ...multi... }$ [more code]',
        "multiNested"                    => '[code] ${ ${...multi nested...}$ }$ [more code]',
        "multiNestedNeedle"              => '[code] { ${ ...multi needles... }# }$ [more code]',
        "multiNestedNeedleAndHayStarter" => '[code] { #{ { ...multi needles and hayStarters... }# }$ }$ [more code]',
    ];

    protected array $skipTestString = [
        "normal"                          => '[code] "string oj string" [more code]',
        "escape"                          => '[code] "string \"oj string" [more code]',
        "normalSingleApostrophe"          => "[code] 'string oj string' [more code]",
        "escapeSingleApostrophe"          => "[code] 'string \'oj string' [more code]",
        "templateLiteral"                 => '[code] `string ${var} string` [more code]',
        "templateLiteralEscape"           => '[code] `string \` ${var} string` [more code]',
        "templateLiteralStrict"           => '[code] `string ${object[`key`]} string` [more code]',
        "templateLiteralDoubleBrackets"   => '[code] `asd ${(() => {return 2 + 2})()} `  [more code]',
        "templateLiteralStringInTemplate" => '[code] `asd ${ " my string -> ` "} `  [more code]',
    ];

    public function testIfUtf8StringIsProperlyReversed(): void
    {
        $string = "óźć";
        $res = Str::utf8rev($string);
        $this->assertEquals("ćźó", $res);
    }

    /**
     * @dataProvider getBlockTestCases
     */
    public function testIfBlockIsProperlySkipped(
        string|array $needle, int $start, string $case, string|array|null $heyStarter, int $expectedPos, string $expectedKey
    ): void {
        list($pos, $key) = Str::skipBlock($needle, $start, new Content($case), $heyStarter);
        $this->assertEquals($expectedPos, $pos);
        $this->assertEquals($expectedKey, $key);
    }

    public function getBlockTestCases(): array
    {
        return [
            ['}' , 8, $this->skipTestBlock['normal'     ], null, 26, '}'],
            ['}' , 8, $this->skipTestBlock['nested'     ], '{' , 27, '}'],
            ['}$', 9, $this->skipTestBlock['multi'      ], null, 24, '}$'],
            ['}$', 9, $this->skipTestBlock['multiNested'], '${', 35, '}$'],
            [['}#', '}$', '}'], 8, $this->skipTestBlock['multiNestedNeedle'], '${', 37, '}$'],
            [['}#', '}$', '}'], 8, $this->skipTestBlock['multiNestedNeedleAndHayStarter'], ['${', '{', '#{'], 58, '}$'],
        ];
    }

    /**
     * @dataProvider getStringTestCases
     */
    public function testIfStringIsProperlySkipped(string $stringType, int $landmarkPos, string $case, bool $backwards, int $expected): void
    {
        $pos = Str::skip($stringType, $landmarkPos, new Content($case), $backwards);
        $this->assertEquals($expected, $pos);
    }

    public function getStringTestCases(): array
    {
        return [
            ['"',  7, $this->skipTestString["normal"                         ], false, 25],
            ['"', 24, $this->skipTestString["normal"                         ], true ,  6],
            ['"',  7, $this->skipTestString["escape"                         ], false, 27],
            ['"', 26, $this->skipTestString["escape"                         ], true ,  6],
            ["'",  7, $this->skipTestString["normalSingleApostrophe"         ], false, 25],
            ["'", 24, $this->skipTestString["normalSingleApostrophe"         ], true ,  6],
            ["'",  7, $this->skipTestString["escapeSingleApostrophe"         ], false, 27],
            ["'", 26, $this->skipTestString["escapeSingleApostrophe"         ], true ,  6],
            ['`',  7, $this->skipTestString["templateLiteral"                ], false, 29],
            ['`', 28, $this->skipTestString["templateLiteral"                ], true ,  6],
            ['`',  7, $this->skipTestString["templateLiteralEscape"          ], false, 32],
            ['`', 31, $this->skipTestString["templateLiteralEscape"          ], true ,  6],
            ['`',  7, $this->skipTestString["templateLiteralStrict"          ], false, 39],
            ['`', 38, $this->skipTestString["templateLiteralStrict"          ], true ,  6],
            ['`',  7, $this->skipTestString["templateLiteralDoubleBrackets"  ], false, 41],
            ['`', 40, $this->skipTestString["templateLiteralDoubleBrackets"  ], true ,  6],
            ['`',  7, $this->skipTestString["templateLiteralStringInTemplate"], false, 36],
            ['`', 35, $this->skipTestString["templateLiteralStringInTemplate"], true ,  6],
        ];
    }

    public function testIfSnakeCaseIsProperlyPascalized(): void
    {
        $nameBefore = "snake_case_name";
        $nameAfter  = "SnakeCaseName";
        $pascalized = Str::pascalize($nameBefore);
        $this->assertEquals($nameAfter, $pascalized);
    }

    public function testIfCamelCaseIsProperlyPascalized(): void
    {
        $nameBefore = "camelCaseName";
        $nameAfter  = "CamelCaseName";
        $pascalized = Str::pascalize($nameBefore);
        $this->assertEquals($nameAfter, $pascalized);
    }

    public function testIfTrueIsProperlyTransformedToString(): void
    {
        $this->assertEquals('true', Str::bool(true));
    }

    public function testIfFalseIsProperlyTransformedToString(): void
    {
        $this->assertEquals('false', Str::bool(false));
    }

    /**
     * @dataProvider getNextLetterTestCases
     */
    public function testIfNextLetterIsFoundProperly(string $case, int $posStart, string $expectedLetter, int $expectedPos): void
    {
        list($letter, $pos) = Str::getNextLetter($posStart, new Content($case));
        $this->assertEquals($expectedLetter, $letter);
        $this->assertEquals($expectedPos, $pos);
    }

    public function getNextLetterTestCases(): array
    {
        return [
            [' startend ', 6, 'e', 6],
            [' start end ', 6, 'e', 7],
            [" start \n end ", 6, 'e', 9],
        ];
    }

    /**
     * @dataProvider getPreviousLetterTestCases
     */
    public function testIfPreviousLetterIsFoundIProperly(string $case, int $posStart, string $expectedLetter, int $expectedPos): void
    {
        list($letter, $pos) = Str::getPreviousLetter($posStart, new Content($case));
        $this->assertEquals($expectedLetter, $letter);
        $this->assertEquals($expectedPos, $pos);
    }

    public function getPreviousLetterTestCases(): array
    {
        return [
            [' startend ', 5, 't', 5],
            [' start end ', 6, 't', 5],
            [" start \n end ", 8, 't', 5],
        ];
    }

    /**
     * @dataProvider getPreviousWordCases
     */
    public function testIfPreviousWordIsFoundProperly(int $start, string $case, bool $startSearch, string $expectedWord, int $expectedPos): void
    {
        list($word, $pos) = Str::getPreviousWord($start, new Content($case), $startSearch);
        $this->assertEquals($expectedWord, $word);
        $this->assertEquals($expectedPos, $pos);
    }

    public function getPreviousWordCases(): array
    {
        return [
            [10, 'word1 word2' , false, 'word1', 0],
            [ 9, 'word1 word2' , true , 'word' , 6],
            [ 6, ' word1 word2', false, 'word1', 1],
            [ 5, 'word1 word2' , false, 'word1', 0],
            [ 8, 'wo.rd1 word2', false, 'rd1'  , 3],
            [ 6, 'wo.rd1 word2', true , 'rd1'  , 3],
        ];
    }

    /**
     * @dataProvider getNextWordCases
     */
    public function testIfNextWordIsFoundProperly(int $start, string $case, bool $startSearch, string $expectedWord, int $expectedPos): void
    {
        list($word, $pos) = Str::getNextWord($start, new Content($case), $startSearch);
        $this->assertEquals($expectedWord, $word);
        $this->assertEquals($expectedPos, $pos);
    }

    public function getNextWordCases(): array
    {
        return [
            [3, 'word1 word2' , false, 'word2', 10],
            [1, 'word1 word2' , true , 'ord1' , 4 ],
            [3, 'word1 word2 ', false, 'word2', 10],
            [5, 'word1 word2' , false, 'word2', 10],
            [3, 'word1 wor.d2', false, 'wor'  , 8 ],
            [5, 'word1 wor.d2', true , 'wor'  , 8 ],
        ];
    }
}
