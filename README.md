sfDependencyInjectionPlugin
===========================

It provides supporting the Symfony's DependencyInjection component in your older symfony (1.4) project with Composer.

How to use
----------

Create the following `composer.json` in your symfony 1.4 project's root.

```json
{
    "config": {
        "vendor-dir": "psr/vendor"
    },
    "autoload": {
        "psr-0": { "": "psr/lib" }
    },
    "require": {
        "symfony/config": "2.3.*",
        "symfony/yaml": "2.3.*",
        "symfony/dependency-injection": "2.3.*"
    }
}
```

Here, Composer would install some vendors into `%SF_ROOT%/psr/vendor`.
Also, You can locate your PSR supported libraries to be auto-loaded in `%SF_ROOT%/psr/lib`.

Install the Composer and install some libraries.

```
$ curl -sS https://getcomposer.org/installer | php
$ php composer.phar install
```

Next, create your `services.yml` in `%SF_ROOT%/config/services.yml` something like:

```yaml
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

Everything is ready. Now, Your `sfContext` has installed Symfony's ServicecContainer, it is called and used as following in your code:

```php
$container = sfContext::getInstance()->getContainer();

// Retrieve the Issei\Tester class which is stored your name "Issei Murasawa"
$tester = $container->get('issei_tester');
```
