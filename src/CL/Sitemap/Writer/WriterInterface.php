<?php

namespace CL\Sitemap\Writer;

use CL\Sitemap\Entry;

interface WriterInterface
{
    const TEMPORARY_EXTENSION = 'xml.tmp';
    const PERMANENT_EXTENSION = 'xml';
    const DEFAULT_MAX_NUMBER_OF_ENTRIES = 49000; // 50000 URLs is the limit, using 49000 to be sure
    const DEFAULT_MAX_FILESIZE = 9.0; // 10 megabytes is the limit, using 9 to be sure

    /**
     * @param Entry $entry
     */
    public function write(Entry $entry);
}