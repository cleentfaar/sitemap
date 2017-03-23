<?php

declare(strict_types=1);

namespace CL\Sitemap\Tests\Writer;

use CL\Sitemap\Entry;
use CL\Sitemap\Type\TypeInterface;
use CL\Sitemap\Writer\TypeWriter;
use DateTime;
use Gaufrette\Adapter\InMemory;
use Gaufrette\Filesystem;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @group functional
 */
class TypeWriterTest extends TestCase
{
    const PRIORITY = 0.5;
    const URL = 'https://www.acme.com/product/1234';
    const LAST_MODIFIED = '2017-01-01';
    const TYPE_NAME = 'my_type';
    const MAX_ENTRY_LIMIT = 49000;
    const MAX_SIZE_LIMIT = 9;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var ObjectProphecy|TypeInterface
     */
    private $type;

    /**
     * @var TypeWriter
     */
    private $typeWriter;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->filesystem = new Filesystem(new InMemory());
        $this->type = $this->prophesize(TypeInterface::class);
        $this->type->getName()->willReturn(self::TYPE_NAME);
        $this->typeWriter = new TypeWriter(
            $this->filesystem,
            $this->type->reveal(),
            self::MAX_ENTRY_LIMIT,
            self::MAX_SIZE_LIMIT
        );
    }

    /**
     * @test
     */
    public function it_creates_at_least_one_part_file()
    {
        $entry = new Entry(
            new Entry\Location(self::URL)
        );

        $this->startWriteAndFinish($entry);

        $path = $this->getPath();

        static::assertTrue($this->filesystem->has($path), 'The type\'s part-file should exist according to the filesystem');

        $file = $this->filesystem->get($path);
        $content = str_replace(PHP_EOL, '', $file->getContent());

        static::assertTrue($file->exists(), 'The type\'s part-file should reflect that it exists');
        static::assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', $content);
        static::assertStringEndsWith('</urlset>', $content);
    }

    /**
     * @test
     */
    public function it_can_write_an_entry_without_optional_fields()
    {
        $entry = new Entry(
            new Entry\Location(self::URL)
        );

        $this->startWriteAndFinish($entry);

        $path = $this->getPath();
        $file = $this->filesystem->get($path);
        $content = str_replace(PHP_EOL, '', $file->getContent());

        static::assertContains('<loc>' . self::URL . '</loc>', $content);
        static::assertNotContains('<changefreq>', $content);
        static::assertNotContains('<lastmod>', $content);
        static::assertNotContains('<priority>', $content);
    }

    /**
     * @test
     */
    public function it_can_write_an_entry_with_optional_fields()
    {
        $entry = new Entry(
            new Entry\Location(self::URL), Entry\ChangeFrequency::daily(), new Entry\LastModified(new DateTime(self::LAST_MODIFIED)), new Entry\Priority(self::PRIORITY)
        );

        $this->startWriteAndFinish($entry);

        $path = $this->getPath();
        $file = $this->filesystem->get($path);
        $content = str_replace(PHP_EOL, '', $file->getContent());

        static::assertContains('<loc>' . self::URL . '</loc>', $content);
        static::assertContains('<changefreq>daily</changefreq>', $content);
        static::assertContains('<priority>'.self::PRIORITY.'</priority>', $content);
        static::assertContains('<lastmod>'.self::LAST_MODIFIED.'</lastmod>', $content);
    }

    /**
     * @test
     */
    public function it_can_rotate_when_url_limit_is_reached()
    {
        $this->typeWriter = new TypeWriter(
            $this->filesystem,
            $this->type->reveal(),
            1,
            self::MAX_SIZE_LIMIT
        );

        $entry = new Entry(
            new Entry\Location(self::URL),
            Entry\ChangeFrequency::daily(),
            new Entry\LastModified(new DateTime(self::LAST_MODIFIED)),
            new Entry\Priority(self::PRIORITY)
        );

        $temporaryPath1 = $this->getTemporaryPath(0);
        $temporaryPath2 = $this->getTemporaryPath(1);

        $this->typeWriter->start();

        $this->typeWriter->write($entry);

        static::assertTrue($this->filesystem->has($temporaryPath1), 'The first (temporary) part-file should exist');
        static::assertFalse($this->filesystem->has($temporaryPath2), 'The second (temporary) part-file should not exist yet');

        // write one more entry, reaching the maximum limit of urls...
        $this->typeWriter->write($entry);

        static::assertTrue($this->filesystem->has($temporaryPath2), 'The second (temporary) part-file should exist');

        $this->typeWriter->finish();

        $path1 = $this->getPath(0);
        $path2 = $this->getPath(1);

        static::assertTrue($this->filesystem->has($path1), 'The first (permanent) part-file should exist');
        static::assertTrue($this->filesystem->has($path2), 'The second (permanent) part-file should exist');
        static::assertFalse($this->filesystem->has($temporaryPath1), 'The first (temporary) part-file should have been renamed');
        static::assertFalse($this->filesystem->has($temporaryPath2), 'The second (temporary) part-file should have been renamed');
    }

    /**
     * @test
     */
    public function it_can_rotate_when_size_limit_is_reached()
    {
        $this->typeWriter = new TypeWriter(
            $this->filesystem,
            $this->type->reveal(),
            self::MAX_ENTRY_LIMIT,
            0.00001
        );

        $entry = new Entry(
            new Entry\Location(self::URL),
            Entry\ChangeFrequency::daily(),
            new Entry\LastModified(new DateTime(self::LAST_MODIFIED)),
            new Entry\Priority(self::PRIORITY)
        );

        $temporaryPath1 = $this->getTemporaryPath(0);
        $temporaryPath2 = $this->getTemporaryPath(1);

        $this->typeWriter->start();
        $this->typeWriter->write($entry);

        static::assertTrue($this->filesystem->has($temporaryPath1), 'The first (temporary) part-file should exist');
        static::assertFalse($this->filesystem->has($temporaryPath2), 'The second (temporary) part-file should not exist yet');

        // write one more entry, reaching the maximum limit of urls...
        $this->typeWriter->write($entry);

        static::assertTrue($this->filesystem->has($temporaryPath2), 'The second (temporary) part-file should exist');

        $this->typeWriter->finish();

        $path1 = $this->getPath(0);
        $path2 = $this->getPath(1);

        static::assertTrue($this->filesystem->has($path1), 'The first (permanent) part-file should exist');
        static::assertTrue($this->filesystem->has($path2), 'The second (permanent) part-file should exist');
        static::assertFalse($this->filesystem->has($temporaryPath1), 'The first (temporary) part-file should have been renamed');
        static::assertFalse($this->filesystem->has($temporaryPath2), 'The second (temporary) part-file should have been renamed');
    }

    /**
     * @param Entry $entry
     */
    private function startWriteAndFinish(Entry $entry)
    {
        $this->typeWriter->start();

        $this->typeWriter->write($entry);

        $this->typeWriter->finish();
    }

    /**
     * @param int $part
     *
     * @return string
     */
    private function getPath(int $part = 0): string
    {
        return sprintf('%s/%s_part%d.xml', self::TYPE_NAME, self::TYPE_NAME, $part);
    }

    /**
     * @param int $part
     *
     * @return string
     */
    private function getTemporaryPath(int $part = 0): string
    {
        return sprintf('%s/%s_part%d.xml.tmp', self::TYPE_NAME, self::TYPE_NAME, $part);
    }
}
