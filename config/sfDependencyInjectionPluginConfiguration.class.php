<?php

/*
 * This file is part of the sfDependencyInjectionPlugin package.
 * (c) Issei Murasawa <issei.m7@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * The configuration of sfDependencyInjectionPlugin.
 *
 * @author Issei Murasawa <issei.m7@gmail.com>
 */
class sfDependencyInjectionPluginConfiguration extends sfPluginConfiguration
{
    /**
     * @see sfPluginConfiguration
     */
    public function initialize()
    {
        sfConfig::set('sf_container_class', sfConfig::get('sf_app') . (sfConfig::get('sf_debug') ? 'Debug' : '') . 'Container');

        if ($this->configuration instanceof sfApplicationConfiguration) {
            $this->dispatcher->connect('context.load_factories', array($this, 'onContextLoadFactories'));
        }

        if (0 <= version_compare(SYMFONY_VERSION, '1.5.0')) {
            $this->dispatcher->connect(sfContainerGenerator::CONTAINER_BUILD_EVENT, array($this, 'onContainerBuild'));
        }
    }

    public function onContextLoadFactories(sfEvent $event)
    {
        /** @var sfContext $context */
        $context = $event->getSubject();

        if (0 <= version_compare(SYMFONY_VERSION, '1.5.0')) {
            $container = $context->getServiceContainer();
        } else {
            require $this->configuration->getConfigCache()->checkConfig('config/services.yml');

            $containerClass = sfConfig::get('sf_container_class');
            $container = new $containerClass();
        }

        $context->set('container', $container);
    }

    public function onContainerBuild(sfEvent $event)
    {
        /** @var ContainerBuilder $container */
        $container = $event->getSubject();

        $container->addObjectResource($this);

        $container->register('sf_event_dispatcher', 'sfEventDispatcher')->setSynthetic(true);
        $container->register('sf_formatter', 'sfFormatter')->setSynthetic(true);
        $container->register('sf_user', 'sfUser')->setSynthetic(true);
        $container->register('sf_routing', 'sfUser')->setSynthetic(true);
    }
}
