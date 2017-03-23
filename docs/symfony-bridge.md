# Symfony bridge

If your site is built using [Symfony Framework](https://symfony.com), you can take advantage
of the following bridges.

### `RegisterTypesPass`

If you are using the `TypeRegistry` or `Generator`, and have a lot of types 
you want to generate, you may want to register this compiler pass in your bundle.

Let's assume you have the following services setup:

```yaml
services:
    app.filesystem.sitemaps_adapter:
        class: Gaufrette\Adapter\Local
        
    app.filesystem.sitemaps:
        class: Gaufrette\Filesystem
        arguments:
            - '@app.filesystem.sitemaps_adapter'
        
    # ...
    
    app.sitemap.type_registry:
        class: CL\Sitemap\TypeRegistry
        
    app.sitemap.index_entry_resolver:
        class: CL\Sitemap\SimpleIndexEntryResolver
        arguments:
            - 'https://www.acme.com/sitemaps'
            
    app.sitemap.generator:
        class: CL\Sitemap\Generator
        arguments:
            - '@app.filesystem.sitemaps'
            - '@app.sitemap.type_registry'
            - '@app.sitemap.index_entry_resolver'
```

Your type registry's service ID being `app.sitemap.type_registry`, you can now add the compiler pass like this:

```php
<?php

namespace AppBundle;

use CL\Sitemap\Bridge\Symfony\DependencyInjection\Compiler\RegisterTypesPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AppBundle extends Bundle
{
    /**
     * @inheritdoc
     */
    public function build(ContainerBuilder $container)
    {
        $registryId = 'app.sitemap.type_registry';
        $tagName = 'app.sitemap.type';
        
        $container->addCompilerPass(new RegisterTypesPass($registryId, $tagName));
    }
}
```

That's it, all that's left is to start tagging your type services:
```yaml
services:
    # ...
    app.sitemap.type.products:
        class: AppBundle\Sitemap\Type\ProductType
        tags:
            - {name: app.sitemap.type}
```
