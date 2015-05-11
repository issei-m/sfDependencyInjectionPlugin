<?php

/*
 * This file is part of the sfDependencyInjectionPlugin package.
 * (c) Issei Murasawa <issei.m7@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @author Issei Murasawa <issei.m7@gmail.com>
 */
class sfEventDispatcherRetriever
{
    /**
     * This class cannot be instantiable.
     */
    private function __construct()
    {
    }

    /**
     * Returns the sfEventDispatcher instance.
     *
     * @param mixed $context
     *
     * @return sfEventDispatcher|null
     */
    public static function retrieve($context)
    {
        if ($context instanceof sfContext || $context instanceof sfProjectConfiguration) {
            return $context->getEventDispatcher();
        } elseif (isset($GLOBALS['dispatcher'])) {
            return $GLOBALS['dispatcher'];
        }

        $refClass = new ReflectionClass(get_class($context));

        if ($refClass->hasProperty('dispatcher')) {
            return self::retrieveByDispatcherProperty($refClass, $context);
        } elseif ($refClass->hasProperty('configuration')) {
            return self::retrieveByConfigurationProperty($refClass, $context);
        }
    }

    private static function retrieveByDispatcherProperty(ReflectionClass $refClass, $context)
    {
        $refProp = $refClass->getProperty('dispatcher');
        $refProp->setAccessible(true);

        return $refProp->getValue($context);
    }

    private static function retrieveByConfigurationProperty(ReflectionClass $refClass, $context)
    {
        $refProp = $refClass->getProperty('configuration');
        $refProp->setAccessible(true);

        $configuration = $refProp->getValue($context);

        if ($configuration instanceof sfProjectConfiguration) {
            return $configuration->getEventDispatcher();
        }
    }
}
