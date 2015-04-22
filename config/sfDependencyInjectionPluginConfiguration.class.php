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

        $configuration = $this;

        $this->dispatcher->connect('context.load_factories', function (sfEvent $event) use ($configuration) {
            /** @var sfContext $context */
            $context = $event->getSubject();

            if (0 <= version_compare(SYMFONY_VERSION, '1.5.0')) {
                $container = $context->getServiceContainer();
            } else {
                $configuration->configuration->getConfigCache()->import('config/services.yml');
                $containerClass = sfConfig::get('sf_container_class');
                $container = new $containerClass();
            }

            $context->set('container', $container);
        });

        if (0 <= version_compare(SYMFONY_VERSION, '1.5.0')) {
            $this->dispatcher->connect(sfContainerGenerator::CONTAINER_BUILD_EVENT, function (sfEvent $event) use ($configuration) {
                /** @var ContainerBuilder $container */
                $container = $event->getSubject();

                $container->addObjectResource($configuration);

                $container->register('sf_event_dispatcher', 'sfEventDispatcher')->setSynthetic(true);
                $container->register('sf_formatter', 'sfFormatter')->setSynthetic(true);
                $container->register('sf_user', 'sfUser')->setSynthetic(true);
                $container->register('sf_routing', 'sfUser')->setSynthetic(true);
            });
        }
    }
}
