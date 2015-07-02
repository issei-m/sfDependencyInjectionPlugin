<?php

require_once __DIR__ . '/fixtures/config/ProjectConfiguration.class.php';

class FunctionalTest extends PHPUnit_Framework_TestCase
{
    public function testDebug()
    {
        sfContext::createInstance(sfProjectConfiguration::getApplicationConfiguration('app1', 'test', true));

        $this->assertFileExists(sfConfig::get('sf_app_cache_dir') . '/app1DebugContainer.php');
        $this->assertFileExists(sfConfig::get('sf_app_cache_dir') . '/app1DebugContainer.php.meta');
        $this->assertFileExists(sfConfig::get('sf_app_cache_dir') . '/app1DebugContainer.xml');

        $this->greaterThan(0, $this->countComments(file_get_contents(sfConfig::get('sf_app_cache_dir') . '/app1DebugContainer.php')), 'comments have not been removed');

        /** @var sfContainer $container */
        $container = sfContext::getInstance()->getServiceContainer();
        $this->assertEquals('app1DebugContainer', get_class($container));
        $this->assertEquals('App1 Application', $container->getParameter('name'), '"name" should be expanded');
        $this->assertInstanceOf('TestClass', $container->get('test'));
        $this->assertEquals('A', $container->get('test')->a);
        $this->assertEquals('B', $container->get('test')->b);
        $this->assertEquals('C', $container->get('test')->c);
    }

    public function testNoDebug()
    {
        sfContext::createInstance(sfProjectConfiguration::getApplicationConfiguration('app2', 'test', false));

        $this->assertFileExists(sfConfig::get('sf_app_cache_dir') . '/app2Container.php');
        $this->assertFileNotExists(sfConfig::get('sf_app_cache_dir') . '/app2Container.php.meta');
        $this->assertFileNotExists(sfConfig::get('sf_app_cache_dir') . '/app2Container.xml');

        $this->assertEquals(0, $this->countComments(file_get_contents(sfConfig::get('sf_app_cache_dir') . '/app2Container.php')), 'comments have been removed');

        /** @var sfContainer $container */
        $container = sfContext::getInstance()->getServiceContainer();
        $this->assertEquals('app2Container', get_class($container));
        $this->assertEquals('App2 Application', $container->getParameter('name'), '"name" should be expanded');
        $this->assertEquals('X', $container->get('test')->a);
        $this->assertEquals('Y', $container->get('test')->b);
        $this->assertEquals('Z', $container->get('test')->c);
    }

    public function testSfEventDispatcherRetriever()
    {
        $context = sfContext::createInstance(sfProjectConfiguration::getApplicationConfiguration('app2', 'test', false));
        $dispatcher = $context->getEventDispatcher();

        $this->assertSame($dispatcher, sfEventDispatcherRetriever::retrieve($context));
        $this->assertSame($dispatcher, sfEventDispatcherRetriever::retrieve($context->getConfiguration()));
        $this->assertSame($dispatcher, sfEventDispatcherRetriever::retrieve($context->getConfiguration()->getPluginConfiguration('sfDependencyInjectionPlugin')));
        $this->assertSame($dispatcher, sfEventDispatcherRetriever::retrieve($context->getConfiguration()->getConfigCache()));
        $this->assertNull(sfEventDispatcherRetriever::retrieve(null));

        $GLOBALS['dispatcher'] = $dispatcher;
        $this->assertSame($dispatcher, sfEventDispatcherRetriever::retrieve(null));
        unset($GLOBALS['dispatcher']);
    }

    public function tearDown()
    {
        sfContext::getInstance()->shutdown();
        $testDir = sfConfig::get('sf_cache_dir') . '/..';
        sfToolkit::clearDirectory($testDir);
        @rmdir($testDir);
    }

    private function countComments($source)
    {
        return count(array_filter(
            token_get_all($source),
            function ($token) {
                return is_array($token) && in_array($token[0], array(T_COMMENT, T_DOC_COMMENT));
            }
        ));
    }
}
