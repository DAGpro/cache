<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Tests;

use ReflectionClass;
use ReflectionException;
use ReflectionObject;
use stdClass;

use function is_array;
use function is_object;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Invokes an inaccessible method.
     *
     * @param bool $revoke whether to make method inaccessible after execution
     *
     * @throws ReflectionException
     *
     * @return mixed
     */
    protected function invokeMethod($object, $method, array $args = [], bool $revoke = true)
    {
        $reflection = new ReflectionObject($object);
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);
        $result = $method->invokeArgs($object, $args);

        if ($revoke) {
            $method->setAccessible(false);
        }

        return $result;
    }

    /**
     * Sets an inaccessible object property to a designated value.
     *
     * @param $object
     * @param $propertyName
     * @param $value
     * @param bool $revoke Whether to make property inaccessible after setting.
     */
    protected function setInaccessibleProperty($object, $propertyName, $value, bool $revoke = true): void
    {
        $class = new ReflectionClass($object);

        while (!$class->hasProperty($propertyName)) {
            $class = $class->getParentClass();
        }

        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);

        if ($revoke) {
            $property->setAccessible(false);
        }
    }

    /**
     * Gets an inaccessible object property.
     *
     * @param $object
     * @param $propertyName
     * @param bool $revoke Whether to make property inaccessible after getting.
     *
     * @return mixed
     */
    protected function getInaccessibleProperty($object, $propertyName, bool $revoke = true)
    {
        $class = new ReflectionClass($object);
        while (!$class->hasProperty($propertyName)) {
            $class = $class->getParentClass();
        }

        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);
        $result = $property->getValue($object);

        if ($revoke) {
            $property->setAccessible(false);
        }

        return $result;
    }

    public static function dataProvider(): array
    {
        $object = new stdClass();
        $object->test_field = 'test_value';

        return [
            'integer' => ['test_integer', 1],
            'double' => ['test_double', 1.1],
            'string' => ['test_string', 'a'],
            'boolean_true' => ['test_boolean_true', true],
            'boolean_false' => ['test_boolean_false', false],
            'object' => ['test_object', $object],
            'array' => ['test_array', ['test_key' => 'test_value']],
            'null' => ['test_null', null],
            'supported_key_characters' => ['AZaz09_.', 'b'],
            '64_characters_key_max' => ['bVGEIeslJXtDPrtK.hgo6HL25_.1BGmzo4VA25YKHveHh7v9tUP8r5BNCyLhx4zy', 'c'],
            'string_with_number_key' => ['111', 11],
            'string_with_number_key_1' => ['022', 22],
        ];
    }

    public function getDataProviderData($keyPrefix = ''): array
    {
        $dataProvider = $this->dataProvider();
        $data = [];

        foreach ($dataProvider as $item) {
            $data[$keyPrefix . $item[0]] = $item[1];
        }

        return $data;
    }

    public function assertSameExceptObject($expected, $actual): void
    {
        // assert for all types
        $this->assertEquals($expected, $actual);

        // no more asserts for objects
        if (is_object($expected)) {
            return;
        }

        // asserts same for all types except objects and arrays that can contain objects
        if (!is_array($expected)) {
            $this->assertSame($expected, $actual);
            return;
        }

        // assert same for each element of the array except objects
        foreach ($expected as $key => $value) {
            if (is_object($value)) {
                $this->assertEquals($expected[$key], $actual[$key]);
            } else {
                $this->assertSame($expected[$key], $actual[$key]);
            }
        }
    }
}
