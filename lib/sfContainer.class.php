<?php

/*
 * This file is part of the sfDependencyInjectionPlugin package.
 * (c) Issei Murasawa <issei.m7@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\DependencyInjection\Container;

/**
 * @author Issei Murasawa <issei.m7@gmail.com>
 */
class sfContainer extends Container implements sfServiceContainerInterface
{
    /**
     * {@inheritdoc}
     */
    public function setService($id, $service)
    {
        $this->set($id, $service);
    }

    /**
     * {@inheritdoc}
     */
    public function getService($id)
    {
        return $this->get($id);
    }

    /**
     * {@inheritdoc}
     */
    public function setParameters(array $parameters)
    {
        $this->parameterBag->clear();
        $this->addParameters($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function addParameters(array $parameters)
    {
        foreach ($parameters as $name => $parameter) {
            $this->setParameter($name, $parameter);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return $this->parameterBag->all();
    }

    /**
     * {@inheritdoc}
     */
    public function hasService($name)
    {
        return $this->has($name);
    }
}
