<?php

/*
 * This file is part of the sfDependencyInjectionPlugin package.
 * (c) Issei Murasawa <issei.m7@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;

/**
 * @author Issei Murasawa <issei.m7@gmail.com>
 */
class sfContainer extends Container
{
    /**
     * {@inheritdoc}
     */
    public function hasParameter($name)
    {
        return parent::hasParameter($name) || sfConfig::has($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter($name)
    {
        if (parent::hasParameter($name)) {
            return parent::getParameter($name);
        }

        if (sfConfig::has($name)) {
            return sfConfig::get($name);
        }

        throw new ParameterNotFoundException($name);
    }
}
