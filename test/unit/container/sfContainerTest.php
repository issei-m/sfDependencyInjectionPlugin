<?php

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class sfContainerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var sfContainer
     */
    private $SUT;

    protected function setUp()
    {
        $this->SUT = new sfContainer(new ParameterBag(array(
            'aaa' => 'xxx',
        )));

        sfConfig::set('aaa', 'yyy');
        sfConfig::set('bbb', 'zzz');
    }

    public function testGeneral()
    {
        $this->assertTrue($this->SUT->hasParameter('aaa'));
        $this->assertTrue($this->SUT->hasParameter('bbb'));
        $this->assertFalse($this->SUT->hasParameter('ccc'));
        $this->assertEquals('xxx', $this->SUT->getParameter('aaa'));
        $this->assertEquals('zzz', $this->SUT->getParameter('bbb'));
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException
     */
    public function testGetParameterWithInvalidName()
    {
        $this->SUT->getParameter('ccc');
    }
}
