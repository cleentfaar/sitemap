<?php

declare(strict_types=1);

namespace CL\Sitemap\Tests;

use CL\Sitemap\Entry;
use DateTime;
use PHPUnit\Framework\TestCase;

class EntryTest extends TestCase
{
    /**
     * @test
     */
    public function its_getters_return_expected_values()
    {
        $entry = new Entry(
            $location = new Entry\Location('https://www.acme.com/product/1234'), $changeFrequency = Entry\ChangeFrequency::weekly(), $lastModified = new Entry\LastModified(new DateTime()), $priority = new Entry\Priority(0.5)
        );

        static::assertSame($location, $entry->getLocation());
        static::assertSame($priority, $entry->getPriority());
        static::assertSame($changeFrequency, $entry->getChangeFrequency());
        static::assertSame($lastModified, $entry->getLastModified());
    }
}
