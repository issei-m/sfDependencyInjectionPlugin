<?php

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class sfCompatibleWithSymfony15ContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testGeneral()
    {
        $container = new sfCompatibleWithSymfony15Container(new ParameterBag(['one' => 1]));
        $this->assertFalse($container->hasService('foo_service'));
        $this->assertSame(['one' => 1], $container->getParameters());

        $container->setService('foo_service', $fooService = new \stdClass());
        $container->addParameters(['two' => 2, 'three' => 3]);
        $this->assertTrue($container->hasService('foo_service'));
        $this->assertSame($fooService, $container->getService('foo_service'));
        $this->assertSame(['one' => 1, 'two' => 2, 'three' => 3], $container->getParameters());

        $container->setParameters(['ten' => 10]);
        $this->assertSame(['ten' => 10], $container->getParameters());
    }
}
