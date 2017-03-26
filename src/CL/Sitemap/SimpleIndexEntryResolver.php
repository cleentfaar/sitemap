<?php

declare(strict_types=1);

namespace CL\Sitemap;

use Assert\Assertion;
use CL\Sitemap\Entry\LastModified;
use CL\Sitemap\Entry\Location;

class SimpleIndexEntryResolver implements IndexEntryResolverInterface
{
    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @param string $baseUrl
     */
    public function  __construct(string $baseUrl)
    {
        Assertion::url($baseUrl);

        $this->baseUrl = $baseUrl;
    }

    /**
     * @inheritdoc
     */
    public function resolve(string $path, int $modifiedTimestamp): Entry
    {
        return new Entry(
            new Location(sprintf('%s/%s', $this->baseUrl, $path)),
            null,
            LastModified::fromUnixTimestamp($modifiedTimestamp)
        );
    }
}
