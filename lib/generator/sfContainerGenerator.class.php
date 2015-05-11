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

    public function __construct(array $configPaths, $debug, sfEventDispatcher $eventDispatcher = null)
    {
        $this->dispatcher = $eventDispatcher;
        $this->config     = sfDefineEnvironmentConfigHandler::getConfiguration($configPaths);
        $this->debug      = (bool) $debug;

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
        $container = $this->buildContainer();
        $container->compile();

        $this->dumpContainer($container, $cache, $baseClass);
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
        $content = $dumper->dump(array('class' => stristr(basename($cache), '.', true), 'base_class' => $baseClass));

        if (!$this->debug) {
            $content = $this->stripComments($content);
        }

        $cache->write($content, array_merge($this->resources, $container->getResources()));
    }

    private function stripComments($content)
    {
        if (class_exists('Symfony\Component\HttpKernel\Kernel')) {
            $content = Kernel::stripComments($content);
        }

        return $content;
    }
}
