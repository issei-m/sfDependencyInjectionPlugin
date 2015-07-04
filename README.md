sfDependencyInjectionPlugin
===========================

[![Build Status](https://travis-ci.org/issei-m/sfDependencyInjectionPlugin.svg?branch=master)](https://travis-ci.org/issei-m/sfDependencyInjectionPlugin)

Provides integration Symfony2's Dependency Injection component with your older symfony (1.4+) project.

Installation
------------

Using Composer would be best way:

```
$ composer require issei-m/sf-dependency-injection-plugin
```

Here, Composer would install this plugin in your `plugins` directory and some other libraries plugin depends on into `vendor`.

If you don't use Composer, you need to install this plugin and some others manually.

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

The `services.yml` is supporting the configuratoin cascade like the `settings.yml`, and it can be located in several different `config` directory for apps (e.g.`apps/frontend/config`).
When the ServiceContainer is compiled, the values from these are merged.

Next, enable this plugin at your `ProjectConfiguration`:

```php
class ProjectConfiguration extends sfProjectConfiguration
{
  public function setup()
  {
    $this->enablePlugins('sfDependencyInjectionPlugin');
    ...
  }
}
```

Now, your `sfContext` has installed Symfony's service container, it is used as following in your code:

```php
// Get the ServiceContainer.
$container = sfContext::getInstance()->getContainer();

// Retrieve the NewsletterManager class which was initialized with the Mailer.
$newsletterManager = $container->get('newsletter_manager');
```

If you use [lexpress/symfony1], `sfServiceContainer` is replaced with plugin's service container. But it might work almost as well as framework's one:

```php
$container = sfContext::getInstance()->getServiceContainer();
$newsletterManager = sfContext::getInstance()->getService('newsletter_manager');
```

### At task

Even though you don't initialize the sfContext at task, you can initialize the service container manually like this:
  
```php
$containerClass = require $this->configuration->getConfigCache()->checkConfig('config/services.yml', true);
$container = new $containerClass();
```

Event
-----

When container is compiled, `service_container.build` event is fired. You can expand container definitions if you subscribe this event.

It means you can have control your service container as you wish with your own extension, compiler pass etc...:
 
```php
class ProjectConfiguration extends sfProjectConfiguration
{
  public function setup()
  {
    $this->dispatcher->connect('service_container.build', function (sfEvent $event) {
      /** @var \Symfony\Component\DependencyInjection\ContainerBuilder $container */
      $container = $event->getSubject();
      $container->addObjectResource($this);

      // additional parameter
      $container->setParameter('foo', 'bar');

      // add extension
      $container->registerExtension(new YourExtension());

      // add compiler pass
      $container->addCompilerPass(new YourPass());
    });
  }
}
```

[lexpress/symfony1]: https://github.com/LExpress/symfony1
