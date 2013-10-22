<?php

/*
 * This file is part of the sfDependencyInjectionPlugin package.
 * (c) Issei Murasawa <issei.m7@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * The container lib.
 *
 * It is configured by loading the YAML configuration which defined parameters/services each every environment.
 *
 * @author  Issei Murasawa <issei.m7@gmail.com>
 * @package sfDependencyInjectionPlugin
 */
class sfContainerBuilder
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container The base container.
     */
    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container ?: new ContainerBuilder();
    }

    /**
     * Returns the container which has been built from YAML file resources.
     *
     * @param  array $configPaths
     *
     * @return ContainerBuilder
     */
    public function build(array $configPaths)
    {
        foreach ($configPaths as $configPath) {
            $this->container->addResource(new FileResource($configPath));
        }

        $content = sfDefineEnvironmentConfigHandler::getConfiguration($configPaths);

        if (isset($content['parameters'])) {
            foreach ($content['parameters'] as $key => $value) {
                $this->container->setParameter($key, $this->resolveServices($value));
            }
        }

        if (isset($content['services'])) {
            foreach ($content['services'] as $id => $service) {
                $this->parseDefinition($id, $service);
            }
        }

        return $this->container;
    }

    /**
     * Parses a definition.
     *
     * @param string $id
     * @param array  $service
     *
     * @throws InvalidArgumentException When tags are invalid
     *
     * @link https://github.com/symfony/DependencyInjection/blob/v2.3.6/Loader/YamlFileLoader.php#L127
     */
    private function parseDefinition($id, $service)
    {
        if (is_string($service) && 0 === strpos($service, '@')) {
            $this->container->setAlias($id, substr($service, 1));

            return;
        } elseif (isset($service['alias'])) {
            $public = !array_key_exists('public', $service) || (Boolean) $service['public'];
            $this->container->setAlias($id, new Alias($service['alias'], $public));

            return;
        }

        if (isset($service['parent'])) {
            $definition = new DefinitionDecorator($service['parent']);
        } else {
            $definition = new Definition();
        }

        if (isset($service['class'])) {
            $definition->setClass($service['class']);
        }

        if (isset($service['scope'])) {
            $definition->setScope($service['scope']);
        }

        if (isset($service['synthetic'])) {
            $definition->setSynthetic($service['synthetic']);
        }

        if (isset($service['synchronized'])) {
            $definition->setSynchronized($service['synchronized']);
        }

        if (isset($service['lazy'])) {
            $definition->setLazy($service['lazy']);
        }

        if (isset($service['public'])) {
            $definition->setPublic($service['public']);
        }

        if (isset($service['abstract'])) {
            $definition->setAbstract($service['abstract']);
        }

        if (isset($service['factory_class'])) {
            $definition->setFactoryClass($service['factory_class']);
        }

        if (isset($service['factory_method'])) {
            $definition->setFactoryMethod($service['factory_method']);
        }

        if (isset($service['factory_service'])) {
            $definition->setFactoryService($service['factory_service']);
        }

        if (isset($service['file'])) {
            $definition->setFile($service['file']);
        }

        if (isset($service['arguments'])) {
            $definition->setArguments($this->resolveServices($service['arguments']));
        }

        if (isset($service['properties'])) {
            $definition->setProperties($this->resolveServices($service['properties']));
        }

        if (isset($service['configurator'])) {
            if (is_string($service['configurator'])) {
                $definition->setConfigurator($service['configurator']);
            } else {
                $definition->setConfigurator(array($this->resolveServices($service['configurator'][0]), $service['configurator'][1]));
            }
        }

        if (isset($service['calls'])) {
            foreach ($service['calls'] as $call) {
                $args = isset($call[1]) ? $this->resolveServices($call[1]) : array();
                $definition->addMethodCall($call[0], $args);
            }
        }

        if (isset($service['tags'])) {
            if (!is_array($service['tags'])) {
                throw new InvalidArgumentException(sprintf('Parameter "tags" must be an array for service "%s".', $id));
            }

            foreach ($service['tags'] as $tag) {
                if (!isset($tag['name'])) {
                    throw new InvalidArgumentException(sprintf('A "tags" entry is missing a "name" key for service "%s".', $id));
                }

                $name = $tag['name'];
                unset($tag['name']);

                foreach ($tag as $attribute => $value) {
                    if (!is_scalar($value)) {
                        throw new InvalidArgumentException(sprintf('A "tags" attribute must be of a scalar-type for service "%s", tag "%s".', $id, $name));
                    }
                }

                $definition->addTag($name, $tag);
            }
        }

        $this->container->setDefinition($id, $definition);
    }

    /**
     * Resolves services.
     *
     * @param string $value
     *
     * @return Reference
     *
     * @link https://github.com/symfony/DependencyInjection/blob/v2.3.6/Loader/YamlFileLoader.php#L310
     */
    private function resolveServices($value)
    {
        if (is_array($value)) {
            $value = array_map(array($this, 'resolveServices'), $value);
        } elseif (is_string($value) &&  0 === strpos($value, '@')) {
            if (0 === strpos($value, '@@')) {
                $value = substr($value, 1);
                $invalidBehavior = null;
            } elseif (0 === strpos($value, '@?')) {
                $value = substr($value, 2);
                $invalidBehavior = ContainerInterface::IGNORE_ON_INVALID_REFERENCE;
            } else {
                $value = substr($value, 1);
                $invalidBehavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE;
            }

            if ('=' === substr($value, -1)) {
                $value = substr($value, 0, -1);
                $strict = false;
            } else {
                $strict = true;
            }

            if (null !== $invalidBehavior) {
                $value = new Reference($value, $invalidBehavior, $strict);
            }
        }

        return $value;
    }
}
