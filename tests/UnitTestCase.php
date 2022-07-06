<?php

namespace YivicTestInpsyde\Wp\Plugin\Tests;

use Codeception\Test\Unit;
use ReflectionClass;
use ReflectionMethod;

class UnitTestCase extends Unit {
    /**
     * @param $className
     * @param $constructorArguments
     * @param $methods
     * @param $sutMethod
     *
     * @return \PHPUnit\Framework\MockObject\MockBuilder
     */
    protected function buildTesteeMock(
        $className,
        $constructorArguments,
        $methods,
        $sutMethod
    ) {

        $testee = $this->getMockBuilder($className);
        $constructorArguments
            ? $testee->setConstructorArgs($constructorArguments)
            : $testee->disableOriginalConstructor();

        $methods and $testee->setMethods($methods);
        $sutMethod and $testee->setMethodsExcept([$sutMethod]);

        return $testee;
    }

    /**
     * Retrieve a Testee Mock to Test Protected Methods
     *
     * @param string $className
     * @param array $constructorArguments
     * @param string $method
     * @param array $methods
     *
     * @return array
     * @throws \ReflectionException
     */
    protected function buildTesteeMethodMock(
        $className,
        $constructorArguments,
        $method,
        $methods
    ) {

        $testee = $this->buildTesteeMock(
            $className,
            $constructorArguments,
            $methods,
            ''
        )->getMock();
        $reflectionMethod = new ReflectionMethod($className, $method);
        $reflectionMethod->setAccessible(true);

        return [
            $testee,
            $reflectionMethod,
        ];
    }

    /**
     * @param $obj
     * @param $prop
     *
     * @return mixed
     * @throws \ReflectionException
     */
    protected function accessProtected($obj, $prop)
    {
        $reflection = new ReflectionClass($obj);
        $property = $reflection->getProperty($prop);
        $property->setAccessible(true);

        return $property->getValue($obj);
    }
}
