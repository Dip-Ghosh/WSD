<?php
declare(strict_types=1);

namespace AppUnitBaseTests;

use PHPUnit\Framework\MockObject\MockObject;

abstract class AbstractAppBaseUnitTestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Returns a test double for the specified class.
     *
     * @param string|string[] $originalClassName
     *
     * @return MockObject
     * @throws null
     */
    protected function createMock(string $originalClassName): MockObject
    {
        return parent::createMock($originalClassName);
    }


    /**
     * Invoke a method (mostly private or protected) against an object
     *
     * @param object $object
     * @param string $methodName
     * @param mixed _parameters optional
     *
     * @return mixed
     * @throws \ReflectionException
     */
    protected function invokeMethod($object, string $methodName)
    {
        $reflectionMethod = new \ReflectionMethod($object, $methodName);
        $reflectionMethod->setAccessible(true);
        $args = func_get_args();
        array_shift($args);
        array_shift($args);
        if ($args) {
            return $reflectionMethod->invoke($object, ...$args);
        }
        else {
            return $reflectionMethod->invoke($object);
        }
    }


    /**
     * Invoke a method (mostly private or protected) against an object with parameters, they can
     * be passed by reference (some or all)
     *
     * @param object $object
     * @param string $methodName
     * @param array  $parameters   like: [
     *                             0 => &$param1ByReference,
     *                             1 => $param2ByValue,
     *                             ]
     *
     * @return mixed
     * @throws \ReflectionException
     */
    protected function invokeMethodParametersByReference($object, string $methodName, array &$parameters)
    {
        $reflectionMethod = new \ReflectionMethod($object, $methodName);
        $reflectionMethod->setAccessible(true);
        return $reflectionMethod->invoke($object, ...$parameters);
    }


    /**
     * @param string|object $classNameOrObject can't restrict, string is to set value for static properties
     * @param string        $propertyName
     * @param mixed         $value
     *
     * @throws \ReflectionException
     */
    protected function setPropertyValue($classNameOrObject, string $propertyName, $value): void
    {
        $reflectionProperty = new \ReflectionProperty($classNameOrObject, $propertyName);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($classNameOrObject, $value);
    }


    /**
     * @param object $object
     * @param string $propertyName
     *
     * @return mixed
     * @throws \ReflectionException
     */
    protected function getPropertyValue($object, string $propertyName)
    {
        $reflectionProperty = new \ReflectionProperty($object, $propertyName);
        $reflectionProperty->setAccessible(true);
        return $reflectionProperty->getValue($object);
    }
}