<?php

/*
 * This file is part of the sfDependencyInjectionPlugin package.
 * (c) Issei Murasawa <issei.m7@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\ConfigCache;

/**
 * The configuration of sfDependencyInjectionPlugin.
 *
 * Integration with Symfony's DependencyInjection component like the HttpKernel component.
 *
 * @package    sfDependencyInjectionPlugin
 * @subpackage config
 * @author     Issei Murasawa <issei.m7@gmail.com>
 * @link       https://github.com/symfony/DependencyInjection
 */
class sfDependencyInjectionPluginConfiguration extends sfPluginConfiguration
{
    /**
     * @see sfPluginConfiguration
     */
    public function initialize()
    {
        $this->dispatcher->connect('context.load_factories', array($this, 'setContextContainer'));
    }

    /**
     * Sets the service container in context.
     *
     * @param sfEvent $event
     */
    public function setContextContainer(sfEvent $event)
    {
        if (sfConfig::get('sf_debug') && sfConfig::get('sf_logging_enabled')) {
            $timer = sfTimerManager::getTimer('Initialize the ServiceContainer');
        }
  
        $context = $event->getSubject();
        $context->set('container', $this->initializeContainer());
  
        if (sfConfig::get('sf_debug') && sfConfig::get('sf_logging_enabled')) {
            $timer->addTime();
        }
    }

    /**
     * Initializes the service container.
     *
     * The cached version of the service container is used when fresh, otherwise the
     * container is built.
     *
     * @return ContainerBuilder
     */
    protected function initializeContainer()
    {
        $class = $this->getContainerClass();
        $cache = new ConfigCache(sfConfig::get('sf_app_cache_dir') . '/' . $class . '.php', sfConfig::get('sf_debug'));

        if (!$cache->isFresh()) {
            $container = $this->buildContainer();
            $container->compile();

            $this->dumpContainer($cache, $container, $class, $this->getContainerBaseClass());

            sfContext::getInstance()->getLogger()->info('Initialized the ServiceContainer');
        } else {
            sfContext::getInstance()->getLogger()->info('Loaded the ServiceContainer from cache');
        }

        require_once $cache;

        $container = new $class();

        return $container;
    }

    /**
     * Gets the container class.
     *
     * @return string The container class
     */
    protected function getContainerClass()
    {
        return sfConfig::get('sf_app') . (sfConfig::get('sf_debug') ? 'Debug' : '') . 'Container';
    }

    /**
     * Gets the container's base class.
     *
     * All names except Container must be fully qualified.
     *
     * @return string
     */
    protected function getContainerBaseClass()
    {
        return 'Container';
    }

    /**
     * Builds the service container.
     *
     * @return ContainerBuilder The compiled service container
     *
     */
    protected function buildContainer()
    {
        $builder = new sfContainerBuilder();

        $configPaths = $this->configuration->getConfigPaths('config/services.yml');

        return $builder->build($configPaths);
    }

    /**
     * Dumps the service container to PHP code in the cache.
     *
     * @param ConfigCache      $cache     The config cache
     * @param ContainerBuilder $container The service container
     * @param string           $class     The name of the class to generate
     * @param string           $baseClass The name of the container's base class
     */
    protected function dumpContainer(ConfigCache $cache, ContainerBuilder $container, $class, $baseClass)
    {
        // cache the container
        $dumper = new PhpDumper($container);

        $content = $dumper->dump(array('class' => $class, 'base_class' => $baseClass));
        if (!sfConfig::get('sf_debug')) {
            $content = self::stripComments($content);
        }

        $cache->write($content, $container->getResources());
    }

    /**
     * Removes comments from a PHP source string.
     *
     * We don't use the PHP php_strip_whitespace() function
     * as we want the content to be readable and well-formatted.
     *
     * @param string $source A PHP string
     *
     * @return string The PHP string with the comments removed
     *
     * @link https://github.com/symfony/HttpKernel/blob/v2.3.6/Kernel.php#L752
     */
    public static function stripComments($source)
    {
        if (!function_exists('token_get_all')) {
            return $source;
        }

        $rawChunk = '';
        $output = '';
        $tokens = token_get_all($source);
        for (reset($tokens); false !== $token = current($tokens); next($tokens)) {
            if (is_string($token)) {
                $rawChunk .= $token;
            } elseif (T_START_HEREDOC === $token[0]) {
                $output .= preg_replace(array('/\s+$/Sm', '/\n+/S'), "\n", $rawChunk).$token[1];
                do {
                    $token = next($tokens);
                    $output .= $token[1];
                } while ($token[0] !== T_END_HEREDOC);
                $rawChunk = '';
            } elseif (!in_array($token[0], array(T_COMMENT, T_DOC_COMMENT))) {
                $rawChunk .= $token[1];
            }
        }

        // replace multiple new lines with a single newline
        $output .= preg_replace(array('/\s+$/Sm', '/\n+/S'), "\n", $rawChunk);

        return $output;
    }
}
