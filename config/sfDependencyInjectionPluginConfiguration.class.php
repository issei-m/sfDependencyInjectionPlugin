<?php

/*
 * This file is part of the sfDependencyInjectionPlugin package.
 * (c) Issei Murasawa <issei.m7@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * The configuration of sfDependencyInjectionPlugin.
 *
 * Integration with Symfony's DependencyInjection component like the HttpKernel component.
 *
 * @author Issei Murasawa <issei.m7@gmail.com>
 *
 * @link https://github.com/symfony/DependencyInjection
 */
class sfDependencyInjectionPluginConfiguration extends sfPluginConfiguration
{
    /**
     * @see sfPluginConfiguration
     */
    public function initialize()
    {
        sfConfig::set('sf_container_class', sfConfig::get('sf_app') . (sfConfig::get('sf_debug') ? 'Debug' : '') . 'Container');
        $this->dispatcher->connect('context.load_factories', array($this, 'setContextContainer'));
    }

    /**
     * Sets the service container in context.
     *
     * @param sfEvent $event
     */
    public function setContextContainer(sfEvent $event)
    {
        $context = $event->getSubject();

        if (0 <= version_compare(SYMFONY_VERSION, '1.5.0')) {
            $container = $context->getServiceContainer();
        } else {
            $this->configuration->getConfigCache()->import('config/services.yml');
            $containerClass = sfConfig::get('sf_container_class');
            $container = new $containerClass();
        }

        $context->set('container', $container);
    }
}
