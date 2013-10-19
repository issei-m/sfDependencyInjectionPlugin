sfDependencyInjectionPlugin
===========================

It provides supporting the Symfony's DependencyInjection component in your older symfony (1.4) project with Composer.

How to use
----------

Create the following `composer.json` in your symfony 1.4 project's root.

```json
{
    "config": {
        "vendor-dir": "plugins"
    },
    "autoload": {
        "psr-0": { "": "psr" }
    },
    "require": {
        "issei-m/sf-dependency-injection-plugin": "1.*"
    }
}
```

Here, Composer would install the plugin in your `plugins` directory and some Symfony2 components into `vendor/symfony/`.
Also, You can locate your PSR supported libraries to be auto-loaded in `%SF_ROOT%/psr`.

Install the Composer and install some libraries.

```
$ curl -sS https://getcomposer.org/installer | php
$ php composer.phar install
```

To register the autoloader for libraries installed with composer, you must add this at the top of your ProjectConfiguration class:

``` php
# config/ProjectConfiguration.class.php

// Composer autoload
require_once dirname(__DIR__).'/plugins/autoload.php';

// symfony1 autoload
require_once dirname(__DIR__).'/lib/vendor/symfony/lib/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

class ProjectConfiguration extends sfProjectConfiguration
{
    // ...
}
```

Next, create your `services.yml` in `%SF_ROOT%/config/services.yml` something like:

```yaml
# config/services.yml

parameters:
    your_name: 'Issei Murasawa'

services:
    issei_tester:
        class: Issei\Tester
        calls:
            - [setName, ["%your_name%"]]

```

Edit `ProjectConfiguration` to be enabled this plugins.

```php
class ProjectConfiguration extends sfProjectConfiguration
{
  public function setup()
  {
    $this->enablePlugins('sfDependencyInjectionPlugin');
    ...
```

Everything is ready. Now, Your `sfContext` has installed Symfony's ServiceContainer, it is called and used as following in your code:

```php
$container = sfContext::getInstance()->getContainer();

// Retrieve the Issei\Tester class which is stored your name "Issei Murasawa"
$tester = $container->get('issei_tester');
```
