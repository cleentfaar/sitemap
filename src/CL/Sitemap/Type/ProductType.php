<?php

declare(strict_types=1);

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
