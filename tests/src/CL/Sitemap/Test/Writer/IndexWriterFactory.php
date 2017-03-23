<?php

declare(strict_types=1);

namespace CL\Sitemap\Test\Writer;

use CL\Sitemap\Writer\IndexWriter;
use Gaufrette\Filesystem;

class IndexWriterFactory
{
    const INDEX_FILENAME = 'my_sitemap_index';
    const MAX_NUMBER_OF_URLS = 1234;
    const MAX_FILESIZE = 9;

    /**
     * @param Filesystem $filesystem
     *
     * @return IndexWriter
     */
    public static function create(Filesystem $filesystem): IndexWriter
    {
        return new IndexWriter($filesystem);
    }

    /**
     * @param Filesystem $filesystem
     * @param string     $indexFileName
     *
     * @return IndexWriter
     */
    public static function createWriterWithIndexFilename(Filesystem $filesystem, string $indexFileName): IndexWriter
    {
        return new IndexWriter(
            $filesystem,
            $indexFileName
        );
    }

    /**
     * @param Filesystem $filesystem
     *
     * @return IndexWriter
     */
    public static function createWriterWithExtremelyLowEntryLimit(Filesystem $filesystem): IndexWriter
    {
        return new IndexWriter(
            $filesystem,
            null,
            1 // extremely low value to trigger upon 2nd entry
        );
    }

    /**
     * @param Filesystem $filesystem
     *
     * @return IndexWriter
     */
    public static function createWriterWithExtremelyLowSizeLimit(Filesystem $filesystem): IndexWriter
    {
        return new IndexWriter(
            $filesystem,
            null,
            null,
            0.00001 // extremely low value to trigger upon 2nd entry
        );
    }
}
