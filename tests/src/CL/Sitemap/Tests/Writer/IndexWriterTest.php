<?php

declare(strict_types=1);

namespace CL\Sitemap\Tests\Writer;

use CL\Sitemap\Entry;
use CL\Sitemap\Exception\EntryLimitReachedException;
use CL\Sitemap\Exception\SizeLimitReachedException;
use CL\Sitemap\Test\EntryFactory;
use CL\Sitemap\Test\Writer\IndexWriterFactory;
use CL\Sitemap\Writer\IndexWriter;
use Gaufrette\Adapter\InMemory;
use Gaufrette\Filesystem;
use PHPUnit\Framework\TestCase;

/**
 * @group functional
 */
class IndexWriterTest extends TestCase
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->filesystem = new Filesystem(new InMemory());
    }

    /**
     * @test
     */
    public function it_creates_the_sitemap_index_file()
    {
        $writer = IndexWriterFactory::createWriterWithIndexFilename($this->filesystem, $indexFilename = 'my_sitemap_index');
        $entry = EntryFactory::createMinimalIndexEntry();

        $this->startWriteAndFinish($writer, $entry);

        $path = sprintf('%s.xml', $indexFilename);

        static::assertTrue($this->filesystem->has($path), 'The index file should exist according to the filesystem');

        $file = $this->filesystem->get($path);
        $content = str_replace(PHP_EOL, '', $file->getContent());

        static::assertTrue($file->exists(), 'The index file should reflect that it exists');
        static::assertSame($file->getName(), $path);
        static::assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', $content);
        static::assertStringEndsWith('</sitemapindex>', $content);
    }

    /**
     * @test
     */
    public function it_can_write_a_minimal_entry()
    {
        $writer = IndexWriterFactory::create($this->filesystem);
        $entry = EntryFactory::createMinimalIndexEntry();

        $this->startWriteAndFinish($writer, $entry);

        $path = $this->getPath();
        $file = $this->filesystem->get($path);
        $content = str_replace(PHP_EOL, '', $file->getContent());

        static::assertContains('<loc>' . $entry->getLocation()->toString() . '</loc>', $content);
        static::assertNotContains('<changefreq>', $content);
        static::assertNotContains('<lastmod>', $content);
        static::assertNotContains('<priority>', $content);
    }

    /**
     * @test
     */
    public function it_can_write_a_full_entry()
    {
        $entry = EntryFactory::createFullIndexEntry();

        $this->startWriteAndFinish(IndexWriterFactory::create($this->filesystem), $entry);

        $path = $this->getPath();
        $file = $this->filesystem->get($path);
        $content = str_replace(PHP_EOL, '', $file->getContent());

        static::assertContains(sprintf('<loc>%s</loc>', $entry->getLocation()->toString()), $content);
        static::assertContains(sprintf('<changefreq>%s</changefreq>', $entry->getChangeFrequency()->toString()), $content);
        static::assertContains(sprintf('<priority>%s</priority>', $entry->getPriority()->toFloat()), $content);
        static::assertContains(sprintf('<lastmod>%s</lastmod>', $entry->getLastModified()->toString()), $content);
    }

    /**
     * @test
     */
    public function it_throws_exception_when_url_limit_is_reached()
    {
        $writer = IndexWriterFactory::createWriterWithExtremelyLowEntryLimit($this->filesystem);
        $entry = EntryFactory::createMinimalIndexEntry();

        $writer->start();
        $writer->write($entry);

        $this->expectException(EntryLimitReachedException::class);

        // write one more entry, reaching the maximum limit of urls...
        $writer->write($entry);
    }

    /**
     * @test
     */
    public function it_throws_exception_when_size_limit_is_reached()
    {
        $writer = IndexWriterFactory::createWriterWithExtremelyLowSizeLimit($this->filesystem);
        $entry = EntryFactory::createMinimalIndexEntry();

        $writer->start();

        $writer->write($entry);

        $this->expectException(SizeLimitReachedException::class);

        // write one more entry, reaching the maximum size limit...
        $writer->write($entry);
    }

    /**
     * @param IndexWriter $writer
     * @param Entry       $entry
     */
    private function startWriteAndFinish(IndexWriter $writer, Entry $entry)
    {
        $writer->start();
        $writer->write($entry);
        $writer->finish();
    }

    /**
     * @return string
     */
    private function getPath(): string
    {
        return sprintf('%s.xml', IndexWriter::DEFAULT_INDEX_FILENAME);
    }
}
