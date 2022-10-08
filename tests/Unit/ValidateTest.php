<?php declare(strict_types=1);

namespace Tests\Unit;

use Tetraquark\Validate;
use Tests\BaseTest;

/**
 * @covers \Tetraquark\Validate
 */
class ValidateTest extends BaseTest
{
    /**
     * @dataProvider provideStringChars
     */
    public function testRecognizingStringChars(string $stringChar, bool $excepted): void
    {
        $this->assertEquals($excepted, Validate::isStringChar($stringChar));
    }

    public function provideStringChars(): array
    {
        return [
            ["'", true],
            ["`", true],
            ["\"", true],
            ["s", false],
        ];
    }

    /**
     * @dataProvider provideTemplateLiteralLandmark
     */
    public function testRecognizingTemplateLiteralLandmark(string $letter, string $previousLetter, bool $inString, bool $excepted): void
    {
        $this->assertEquals($excepted, Validate::isTemplateLiteralLandmark($letter, $previousLetter, $inString));
    }

    public function provideTemplateLiteralLandmark(): array
    {
        return [
            ["'", "\\", true, false], // 'ads \' asd'
            ["`", "\\", false, true], // \\' asd`
            ["`", "\\", true, false], // ` asd \` sad`
            ["`", "a", true, true],   // ` asd a`
        ];
    }

    /**
     * @dataProvider provideStringLandmark
     */
    public function testRecognizingStringLandmark(string $letter, string $previousLetter, bool $inString, bool $excepted): void
    {
        $this->assertEquals($excepted, Validate::isStringLandmark($letter, $previousLetter, $inString));
    }

    public function provideStringLandmark(): array
    {
        return [
            ["`", "\\", false, true], // \\' asd`
            ["`", "\\", true, false], // ` asd \` sad`
            ["`", "a", true, true],   // ` asd a`

            ["\"", "\\", false, true], // \\" asd"
            ["\"", "\\", true, false], // " asd \" sad"
            ["\"", "a", true, true],   // " asd a"

            ["'", "\\", false, true], // \\' asd'
            ["'", "\\", true, false], // ' asd \' sad'
            ["'", "a", true, true],   // ' asd a'
        ];
    }
}
