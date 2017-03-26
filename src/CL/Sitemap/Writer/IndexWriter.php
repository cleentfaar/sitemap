<?php

declare(strict_types=1);

namespace CL\Sitemap\Writer;

use CL\Sitemap\Entry;
use CL\Sitemap\Exception\EntryLimitReachedException;
use CL\Sitemap\Exception\SizeLimitReachedException;
use CL\Sitemap\Renderer;
use Gaufrette\Filesystem;
use Gaufrette\Stream;
use Gaufrette\StreamMode;
use RuntimeException;

class IndexWriter implements WriterInterface
{
    const DEFAULT_INDEX_FILENAME = 'index';

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $path;

    /**
     * @var int[]
     */
    private $entriesAdded = [];

    /**
     * @var Stream|null
     */
    private $stream;

    /**
     * @var bool
     */
    private $started = false;

    /**
     * @var bool
     */
    private $finished = false;

    /**
     * @var int
     */
    private $maxEntryLimit;

    /**
     * @var int
     */
    private $maxSizeLimit;

    /**
     * @var Renderer
     */
    private $renderer;

    /**
     * @param Filesystem  $filesystem
     * @param string|null $indexFilename
     * @param int|null    $maxEntryLimit
     * @param float|null  $maxSizeLimit
     */
    public function __construct(
        Filesystem $filesystem,
        string $indexFilename = null,
        int $maxEntryLimit = null,
        float $maxSizeLimit = null
    ) {
        $indexFilename = $indexFilename ?: self::DEFAULT_INDEX_FILENAME;
        $maxEntryLimit = $maxEntryLimit ?: self::DEFAULT_MAX_NUMBER_OF_ENTRIES;
        $maxSizeLimit = $maxSizeLimit ?: self::DEFAULT_MAX_FILESIZE;

        $this->filesystem = $filesystem;
        $this->path = sprintf(
            '%s.%s',
            $indexFilename,
            self::TEMPORARY_EXTENSION
        );
        $this->maxEntryLimit = $maxEntryLimit;
        $this->maxSizeLimit = $maxSizeLimit;
        $this->renderer = new Renderer('sitemapindex');
    }

    public function start()
    {
        $this->assertCanStart();

        $this->started = true;
        $this->finished = false;

        $this->stream = $this->createStream();
    }

    /**
     * @inheritdoc
     */
    public function write(Entry $entry)
    {
        $this->assertCanWrite();
        $this->assertEntryLimitNotReached();
        $this->assertSizeLimitNotReached();

        $this->stream->write($this->renderer->renderEntry($entry));

        $this->incrementEntriesAdded();
    }

    public function finish()
    {
        $this->assertCanFinish();

        $this->stream->close();

        $newContent = '';
        $newContent .= $this->renderer->renderHeader();
        $newContent .= $this->filesystem->read($this->path);
        $newContent .= $this->renderer->renderFooter();

        $this->filesystem->write($this->path, $newContent, true);

        // remove all current xml files for this type
        $existingIndexPath = $this->replaceExtension($this->path, self::TEMPORARY_EXTENSION, self::PERMANENT_EXTENSION);

        if ($this->filesystem->has($existingIndexPath)) {
            $this->filesystem->delete($existingIndexPath);
        }

        $this->filesystem->rename($this->path, $existingIndexPath);

        $this->filesystem->clearFileRegister();

        $this->finished = true;
    }

    /**
     * @return Stream
     */
    private function createStream(): Stream
    {
        $handle = $this->filesystem->createStream($this->path);

        if (!$handle->open(new StreamMode('a'))) {
            throw new RuntimeException(sprintf('Could not open stream for path: %s', $this->path));
        }

        return $handle;
    }

    private function assertEntryLimitNotReached()
    {
        if (!isset($this->entriesAdded[$this->path])) {
            return false;
        }

        if ($this->entriesAdded[$this->path] >= $this->maxEntryLimit) {
            throw EntryLimitReachedException::withLimit($this->maxEntryLimit, $this->path);
        }
    }

    private function assertSizeLimitNotReached()
    {
        $size = $this->stream->stat()['size'] / (1024 * 1024);

        if ($size >= $this->maxSizeLimit) {
            throw SizeLimitReachedException::withLimit($this->maxSizeLimit, $this->path);
        }
    }

    /**
     * @param string $path
     * @param string $currentExtension
     * @param string $newExtension
     *
     * @return string
     */
    private function replaceExtension(string $path, string $currentExtension, string $newExtension): string
    {
        if (mb_substr($path, -(mb_strlen($currentExtension))) !== $currentExtension) {
            throw new \InvalidArgumentException(sprintf(
                'Can\'t replace the extension, the given path (%s) does not have that extension (%s)',
                $path,
                $currentExtension
            ));
        }

        $newPath = mb_substr($path, 0, -(mb_strlen($currentExtension)));
        $newPath .= $newExtension;

        return $newPath;
    }

    private function incrementEntriesAdded(): void
    {
        if (!isset($this->entriesAdded[$this->path])) {
            $this->entriesAdded[$this->path] = 0;
        }

        ++$this->entriesAdded[$this->path];
    }

    private function assertCanFinish(): void
    {
        if ($this->finished) {
            throw new RuntimeException('You must call start() first');
        }

        if ($this->finished) {
            throw new RuntimeException('Already finished, you need to call start() again');
        }
    }

    private function assertCanWrite(): void
    {
        if (!$this->started) {
            throw new RuntimeException('You must call start() first');
        }
    }

    private function assertCanStart(): void
    {
        if ($this->started) {
            throw new RuntimeException('Already started');
        }
    }
}
