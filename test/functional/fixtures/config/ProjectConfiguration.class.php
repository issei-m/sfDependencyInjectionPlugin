<?php

class ProjectConfiguration extends sfProjectConfiguration
{
    public function setup()
    {
        $tempDir = sys_get_temp_dir() . '/' . uniqid('sf-dependency-injection-plugin-test/', true);

        $this->setCacheDir($tempDir . '/cache');
        $this->setLogDir($tempDir . '/log');

        $this->enablePlugins('sfDependencyInjectionPlugin');
        $this->setPluginPath('sfDependencyInjectionPlugin', __DIR__ . '/../../../..');
    }
}
