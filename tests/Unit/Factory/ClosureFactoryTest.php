<?php declare(strict_types=1);

namespace Tests\Unit\Factory;

use Tetraquark\Model\CustomMethodEssentialsModel;
use Tetraquark\Factory\ClosureFactory;

/**
 * @covers \Tetraquark\Factory\ClosureFactory
 * @uses \Tetraquark\Str::pascalize
 * @uses \Tetraquark\Model\BasePolymorphicModel
 */
class ClosureFactoryTest extends BaseFactory
{
    /**
     * @dataProvider provideForGenerationOfReversalClosure
     */
    public function testGenerationOfReversalClosure(string $negationLetter, string $letter, bool $exceptedRes, array $exceptedData): void
    {
        $essential = new CustomMethodEssentialsModel([
            "data" => [],
            "letter" => $letter
        ]);
        $closure = ClosureFactory::generateReversalClosure($negationLetter);
        $res = $closure($essential);
        $this->assertEquals($exceptedRes, $res);
        $this->assertEquals($exceptedData, $essential->getData());
    }

    public function provideForGenerationOfReversalClosure(): array
    {
        return [
            ['=', 'T', true, ["negation" => "T"]],
            ['=', '=', false, []],
        ];
    }

    /**
     * @dataProvider provideForGenerationOfEqualClosure
     */
    public function testGenerationOfEqualClosure(string $equalLetter, string $letter, bool $exceptedRes): void
    {
        $essential = new CustomMethodEssentialsModel([
            "data" => [],
            "letter" => $letter
        ]);
        $closure = ClosureFactory::generateEqualClosure($equalLetter);
        $res = $closure($essential);
        $this->assertEquals($exceptedRes, $res);
    }

    public function provideForGenerationOfEqualClosure(): array
    {
        return [
            ['=', 'T', false],
            ['=', '=', true],
        ];
    }
}
