# Getting started

Let's start with a very basic example that uses a local filesystem.
 
```php
<?php

namespace Acme\Foobar;

use Acme\Sitemap\Type\ProductType;
use CL\Sitemap\Generator;
use CL\Sitemap\SimpleIndexEntryResolver;
use CL\Sitemap\TypeRegistry;
use Gaufrette\Filesystem;
use Gaufrette\Adapter\Local;

// bootstrapping...
$pathToSitemaps = '/path/to/sitemaps';
$sitemapsBaseUrl = 'https://acme.com/sitemaps';
$filesystem = new Filesystem(new Local($pathToSitemaps));
$indexEntryResolver = new SimpleIndexEntryResolver($sitemapsBaseUrl);
$typeRegistry = new TypeRegistry();
$generator = new Generator($filesystem, $typeRegistry, $indexEntryResolver);

// collect types to write...
$typeRegistry->register(new ProductType());    

// generate the index and type files
$generator->generate();

```

You may have noticed the `ProductType` class used in the example above. 
It is an example of something important on your website that you want to
include in your sitemaps. 

Basically everything you want to list in the sitemap will have it's own type class.

So let's look at an example of how this `ProductType` would look like internally:

```php
<?php

namespace CL\Sitemap\Type;

use CL\Sitemap\Entry;
use Generator;

class ProductType implements TypeInterface
{
    /**
     * @inheritdoc
     */
    public function iterate(): Generator
    {
        // example of some products you could have on your site
        // you probably would get this from your database
        $products = [
            [
                'id' => 1234,
                'name' => 'Chocolate chip cookie',
            ],
            [
                'id' => 5678,
                'name' => 'Oatmeal raisin cookie',
            ],
        ];

        foreach ($products as $product) {
            yield new Entry(
                new Entry\Location(sprintf('https://www.acme.com/product/%d', $product['id'])), Entry\ChangeFrequency::daily(), new Entry\Priority(0.7)
            );
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'products';
    }
}

```

That's it! You now have a type that can generate entries from your products!


### Using the event dispatcher

If you would like to hook into the process of generating the files,
you can make use of the events that are dispatched by the generator.

Here's an example of how you could use this to show some progress bar 
while generating your files:

```php
<?php

namespace Acme\Foobar;

use CL\Sitemap\Generator;
use CL\Sitemap\Type\TypeInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Console\Helper\ProgressBar;

$eventDispatcher = new EventDispatcher();
$eventDispatcher->addListener(Generator::EVENT_TYPE_STARTED, function (TypeInterface $type) {
    $max = 0;
    
    // you could have your types implement countable to
    // make the progressbar useful
    if ($type instanceof \Countable) {
        $max = $type->count();
    }
    
    $this->progress = new ProgressBar($type->getName(), $max);
});
$eventDispatcher->addListener(Generator::EVENT_TYPE_ENTRY_WRITTEN, function (TypeInterface $type) {
    $this->progress->advance();    
});
$eventDispatcher->addListener(Generator::EVENT_TYPE_FINISHED, function (TypeInterface $type) {
    $this->progress->finish();    
});
```

Now we just need to inject your event dispatcher into the generator...

```php
<?php

// ...
$generator = new Generator(
    $filesystem,
    $typeRegistry,
    $indexEntryResolver,
    $eventDispatcher
);
```

And we're done! When you call `$generator->generate()` your listeners will
advance the progressbar for every type entry written!
