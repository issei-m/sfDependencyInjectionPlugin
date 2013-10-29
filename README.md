sfDependencyInjectionPlugin
===========================

It provides supporting the Symfony's DependencyInjection component in your older symfony (1.4) project with Composer.

Installation
------------

Create the following `composer.json` in your symfony 1.4 project's root.

```json
{
    "config": {
        "vendor-dir": "lib/vendor"
    },
    "require": {
        "issei-m/sf-dependency-injection-plugin": "1.*"
    },
    "autoload": {
        "psr-0": { "": "psr" }
    },
}
```

Here, Composer would install the plugin in your `plugins` directory and some Symfony2 components into `vendor/symfony/`.
Also, You can locate your PSR supported libraries to be auto-loaded in `%SF_ROOT%/psr` (optional).

Install the Composer and install some libraries.

```
$ curl -sS https://getcomposer.org/installer | php
$ php composer.phar install
```

To register the autoloader for libraries installed with composer, you must add this at the top of your ProjectConfiguration class:

``` php
# config/ProjectConfiguration.class.php

// Composer autoload
require_once dirname(__DIR__).'/lib/vendor/autoload.php';

// symfony1 autoload
require_once dirname(__DIR__).'/lib/vendor/symfony/lib/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

class ProjectConfiguration extends sfProjectConfiguration
{
    // ...
}
```

Usage
-----

First, create your `services.yml` in `%SF_ROOT%/config/services.yml`. It can be defined your parameters/services to each different environments.

Something like:

```yaml
# config/services.yml

dev:
  parameters:
    mailer.transport: gmail

all:
  parameters:
    # ...
    mailer.transport: sendmail

  services:
    mailer:
      class:     Mailer
      arguments: ["%mailer.transport%"]
    newsletter_manager:
      class:     NewsletterManager
      calls:
        - [setMailer, ["@mailer"]]
```

The `services.yml` is supporting the configuratoin cascade like the `settings.yml`, and it can be located in several different `config` dir. (e.g.`%SF_APP_CONFIG_DIR`)
When the ServiceContainer is compiled, the values from these are merged.

Next, enable this plugin at your `ProjectConfiguration`:

```php
class ProjectConfiguration extends sfProjectConfiguration
{
  public function setup()
  {
    $this->enablePlugins('sfDependencyInjectionPlugin');
    ...
```

Now, your `sfContext` has installed Symfony's ServiceContainer, it is used as following in your code:

```php
// Get the ServiceContainer.
$container = sfContext::getInstance()->getContainer();

// Retrieve the NewsletterManager class which was initialized with the Mailer.
$tester = $container->get('newsletter_manager');
```
