<?php

/*
 * This file is part of the sfDependencyInjectionPlugin package.
 * (c) Issei Murasawa <issei.m7@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Dumper\XmlDumper;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @author Issei Murasawa <issei.m7@gmail.com>
 */
class sfContainerGenerator
{
    const CONTAINER_BUILD_EVENT = 'service_container.build';

    /**
     * @var sfEventDispatcher
     */
    private $dispatcher;
    private $config;
    private $debug;
    private $resources = array();
    private $logging;

    public function __construct(array $configPaths, $debug, sfEventDispatcher $eventDispatcher = null, $logging = false)
    {
        $this->dispatcher = $eventDispatcher;
        $this->config     = sfDefineEnvironmentConfigHandler::getConfiguration($configPaths);
        $this->debug      = (bool) $debug;
        $this->logging    = (bool) $logging;

        foreach ($configPaths as $configPath) {
            $this->resources[] = new FileResource($configPath);
        }
    }

    /**
     * Generates and compiles/dumps the container.
     *
     * @param ConfigCache $cache
     * @param string      $baseClass
     */
    public function generate(ConfigCache $cache, $baseClass = 'Container')
    {
        if ($this->debug && $this->logging) {
            $timer = sfTimerManager::getTimer('Building Service Container');
        }

        $container = $this->buildContainer();
        $container->compile();

        $this->dumpContainer($container, $cache, $baseClass);

        if (isset($timer)) {
            $timer->addTime();
            $this->dispatcher->notify(new sfEvent($this, 'application.log', array('Built service container')));
        }
    }

    private function buildContainer()
    {
        $container = new ContainerBuilder();
        $container->addObjectResource($this);

        $loader = new sfContainerArrayLoader($container);
        $loader->load($this->config);

        if ($this->dispatcher) {
            $this->dispatcher->notify(new sfEvent($container, self::CONTAINER_BUILD_EVENT));
        }

        return $container;
    }

    private function dumpContainer(ContainerBuilder $container, ConfigCache $cache, $baseClass)
    {
        $dumper = new PhpDumper($container);
        $content = $dumper->dump(array('class' => stristr(basename($cache->getPath()), '.', true), 'base_class' => $baseClass));

        if (!$this->debug) {
            $content = $this->stripComments($content);
        }

        $cache->write($content, array_merge($this->resources, $container->getResources()));

        if ($this->debug) {
            $this->dumpForDebug(preg_replace('/\.php/', '.xml', $cache->getPath()), $container);
        }
    }

    private function stripComments($content)
    {
        if (class_exists('Symfony\Component\HttpKernel\Kernel')) {
            $content = Kernel::stripComments($content);
        }

        return $content;
    }

    private function dumpForDebug($filename, ContainerBuilder $container)
    {
        $dumper = new XmlDumper($container);
        $filesystem = new Filesystem();
        $filesystem->dumpFile($filename, $dumper->dump());
    }
}
