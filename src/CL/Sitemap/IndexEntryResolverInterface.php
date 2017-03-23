<?php

declare(strict_types=1);

namespace CL\Sitemap;

interface IndexEntryResolverInterface
{
    /**
     * @param string $path
     * @param int    $modifiedTimestamp
     *
     * @return Entry
     */
    public function resolve(string $path, int $modifiedTimestamp): Entry;
}
