<?php

declare(strict_types=1);

namespace CL\Sitemap\Test;

use CL\Sitemap\Entry;

class EntryFactory
{
    const TYPE_URL = 'https://www.acme.com/sitemap/my_type/my_type_part0.xml';
    const PART_URL = 'https://www.acme.com/product/1234/';
    const PRIORITY = 0.5;
    const LAST_MODIFIED = '2017-01-01';

    /**
     * @param Entry\Location|null $location
     *
     * @return Entry
     */
    public static function createMinimalIndexEntry(Entry\Location $location = null): Entry
    {
        return new Entry(
            $location ?: new Entry\Location(self::TYPE_URL)
        );
    }

    /**
     * @return Entry
     */
    public static function createFullIndexEntry(): Entry
    {
        return new Entry(
            new Entry\Location(self::PART_URL),
            Entry\ChangeFrequency::daily(),
            Entry\LastModified::fromUnixTimestamp(time()),
            new Entry\Priority()
        );
    }

    /**
     * @param Entry\Location|null $location
     *
     * @return Entry
     */
    public static function createMinimalTypeEntry(Entry\Location $location = null): Entry
    {
        return new Entry(
            $location ?: new Entry\Location(self::PART_URL)
        );
    }

    /**
     * @param Entry\Location|null        $location
     * @param Entry\ChangeFrequency|null $changeFrequency
     * @param Entry\LastModified|string  $lastModified
     * @param Entry\Priority|null        $priority
     *
     * @return Entry
     */
    public static function createFullTypeEntry(
        Entry\Location $location,
        Entry\ChangeFrequency $changeFrequency,
        Entry\LastModified $lastModified,
        Entry\Priority $priority
    ): Entry {
        return new Entry(
            $location,
            $changeFrequency,
            $lastModified,
            $priority
        );
    }
}
