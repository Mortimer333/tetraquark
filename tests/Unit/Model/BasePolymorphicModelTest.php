<?php declare(strict_types=1);

namespace Tests\Unit\Model;

use Tests\Unit\BaseModelTest;
use Tetraquark\Model\CustomMethodEssentialsModel;
use Tetraquark\Exception;

/**
 * @covers \Tetraquark\Model\BasePolymorphicModel
 * @covers \Tetraquark\Model\CustomMethodEssentialsModel
 * @covers \Tetraquark\Model\LandmarkResolverModel
 * @covers \Tetraquark\Str::pascalize
 */
class BasePolymorphicModelTest extends BaseModelTest
{
    public function testSetMethodCorrectlyCreatesMethods()
    {
        $data = ["el", "el2"];
        $number = 1000;
        $name = "John Smith";
        $class = new \stdClass();
        $model = new CustomMethodEssentialsModel([
            "data" => $data,
            "number" => $number,
            "name" => $name,
            "class" => $class
        ]);

        $exceptedMethods = [
            "getData", "setData", "appendData", "prependData", "mergeData",
            "getNumber", "setNumber",
            "getName", "setName",
            "getClass", "setClass",
        ];

        $this->assertEqualsCanonicalizing($exceptedMethods, $model->availableGetterAndSetters());
        $this->assertEquals($data, $model->getData());
        $this->assertEquals($name, $model->getName());
        $this->assertEquals($number, $model->getNumber());
        $this->assertEquals($class, $model->getClass());

        $newName = 'Not John Smith';
        $model->setName($newName);
        $this->assertEquals($newName, $model->getName());

        $newEl = "el3";
        $model->appendData($newEl);
        $data = array_merge($data, [$newEl]);
        $this->assertEquals($data, $model->getData());

        $newEl = "el4";
        $model->prependData($newEl);
        $data = array_merge([$newEl], $data);
        $this->assertEquals($data, $model->getData());
    }

    public function testSetThrownExceptionOnNonStringKey(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(400);
        $model = new CustomMethodEssentialsModel([
            "name",
        ]);
    }

    public function testSetThrownExceptionWhenCallingNotExistingMethod(): void
    {
        $model = new CustomMethodEssentialsModel([
            "name" => "Name",
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionCode(500);
        $model->appendName('Surname');
    }

    public function testRetrivingAllDataInFormOfArray(): void
    {
        $set = [
            "data" => ["el", "el2"],
            "number" => 1000,
            "name" => "John Smith",
            "class" =>  new \stdClass()
        ];
        $model = new CustomMethodEssentialsModel($set);

        $res = $model->toArray();
        $this->assertEquals($set, $res);
    }
}
