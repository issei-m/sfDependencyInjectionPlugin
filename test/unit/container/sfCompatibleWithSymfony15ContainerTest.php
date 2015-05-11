<?php

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class sfCompatibleWithSymfony15ContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testGeneral()
    {
        $container = new sfCompatibleWithSymfony15Container(new ParameterBag(array('one' => 1)));
        $this->assertFalse($container->hasService('foo_service'));
        $this->assertSame(array('one' => 1), $container->getParameters());

        $container->setService('foo_service', $fooService = new \stdClass());
        $container->addParameters(array('two' => 2, 'three' => 3));
        $this->assertTrue($container->hasService('foo_service'));
        $this->assertSame($fooService, $container->getService('foo_service'));
        $this->assertSame(array('one' => 1, 'two' => 2, 'three' => 3), $container->getParameters());

        $container->setParameters(array('ten' => 10));
        $this->assertSame(array('ten' => 10), $container->getParameters());
    }
}
