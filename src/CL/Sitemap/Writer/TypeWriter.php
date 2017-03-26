<?php

declare(strict_types=1);

namespace CL\Sitemap\Writer;

use CL\Sitemap\Entry;
use CL\Sitemap\Renderer;
use CL\Sitemap\Type\TypeInterface;
use Gaufrette\Filesystem;
use Gaufrette\Stream;
use Gaufrette\StreamMode;
use RuntimeException;

class TypeWriter implements WriterInterface
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var TypeInterface
     */
    private $type;

    /**
     * @var string
     */
    private $path;

    /**
     * @var array
     */
    private $writtenPaths = [];

    /**
     * @var int
     */
    private $partNumber = 0;

    /**
     * @var array
     */
    private $urlsAdded = [];

    /**
     * @var Stream|null
     */
    private $stream;

    /**
     * @var string
     */
    private $rootDir;

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
     * @var float
     */
    private $maxSizeLimit;

    /**
     * @var Renderer
     */
    private $renderer;

    /**
     * @param Filesystem    $filesystem
     * @param TypeInterface $type
     * @param int           $maxEntryLimit
     * @param float         $maxSizeLimit
     * @param string        $encoding
     */
    public function __construct(
        Filesystem $filesystem,
        TypeInterface $type,
        int $maxEntryLimit = self::DEFAULT_MAX_NUMBER_OF_ENTRIES,
        float $maxSizeLimit = self::DEFAULT_MAX_FILESIZE,
        string $encoding = 'UTF-8'
    ) {
        $this->rootDir = $type->getName();
        $this->filesystem = $filesystem;
        $this->type = $type;
        $this->path = $this->getPath(0);
        $this->maxEntryLimit = $maxEntryLimit;
        $this->maxSizeLimit = $maxSizeLimit;
        $this->renderer = new Renderer('urlset', $encoding);
    }

    /**
     * @inheritdoc
     */
    public function start()
    {
        if ($this->started) {
            throw new RuntimeException('Already started');
        }

        $this->started = true;
        $this->finished = false;

        $this->stream = $this->createStream();
    }

    /**
     * @inheritdoc
     */
    public function write(Entry $entry)
    {
        if (!$this->started) {
            throw new RuntimeException('You must call start() first');
        }

        if ($this->urlLimitReached() || $this->sizeLimitReached()) {
            $this->rotate();
        }

        $this->stream->write($this->renderer->renderEntry($entry));

        if (!in_array($this->path, $this->writtenPaths)) {
            $this->writtenPaths[] = $this->path;
        }

        if (!isset($this->urlsAdded[$this->path])) {
            $this->urlsAdded[$this->path] = 0;
        }

        ++$this->urlsAdded[$this->path];
    }

    /**
     * @inheritdoc
     */
    public function finish(): array
    {
        if ($this->finished) {
            throw new RuntimeException('You must call start() first');
        }

        if ($this->finished) {
            throw new RuntimeException('Already finished, you need to call start() again');
        }

        $this->stream->close();

        $this->applyHeadersAndFooters();
        $this->removeExistingPermanentParts();
        $permanentPaths = $this->makeTemporaryPartsPermanent();
        $this->finished = true;

        return $permanentPaths;
    }

    private function rotate()
    {
        $this->stream->close();

        ++$this->partNumber;

        $this->path = $this->getPath($this->partNumber);

        $this->stream = $this->createStream();
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

    /**
     * @param int $partNumber
     *
     * @return string
     */
    private function getPath(int $partNumber): string
    {
        // the unique id is added so these files can't be found publicly by guessing
        // or, having leaked once, knowing the pattern
        return sprintf(
            '%s/%s_part%d.%s',
            $this->rootDir,
            $this->type->getName(),
            $partNumber,
            self::TEMPORARY_EXTENSION
        );
    }

    /**
     * @return bool
     */
    private function urlLimitReached(): bool
    {
        if (!isset($this->urlsAdded[$this->path])) {
            return false;
        }

        return $this->urlsAdded[$this->path] >= $this->maxEntryLimit;
    }

    /**
     * @return bool
     */
    private function sizeLimitReached(): bool
    {
        $size = $this->stream->stat()['size'];

        return $size / (1024 * 1024) >= $this->maxSizeLimit;
    }

    /**
     * @return string[]
     */
    private function findPartPaths(): array
    {
        return $this->filesystem->listKeys($this->rootDir . '/')['keys'];
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function replaceExtension(string $path): string
    {
        $newPath = substr($path, 0, -(strlen(self::TEMPORARY_EXTENSION)));
        $newPath .= self::PERMANENT_EXTENSION;

        return $newPath;
    }

    /**
     * @param string $path
     * @param string $extension
     *
     * @return bool
     */
    private function pathHasExtension(string $path, string $extension): bool
    {
        return substr($path, -(strlen($extension))) === $extension;
    }

    private function applyHeadersAndFooters()
    {
        foreach ($this->writtenPaths as $path) {
            $newContent = '';
            $newContent .= $this->renderer->renderHeader();
            $newContent .= $this->filesystem->read($path);
            $newContent .= $this->renderer->renderFooter();

            $this->filesystem->write($path, $newContent, true);
        }

        $this->writtenPaths = [];
    }

    private function removeExistingPermanentParts()
    {
        foreach ($this->findPartPaths() as $key) {
            if ($this->pathHasExtension($key, static::PERMANENT_EXTENSION)) {
                $this->filesystem->delete($key);
            }
        }
    }

    /**
     * @return string[]
     */
    private function makeTemporaryPartsPermanent(): array
    {
        $paths = [];

        foreach ($this->findPartPaths() as $key) {
            if ($this->pathHasExtension($key, static::TEMPORARY_EXTENSION)) {
                // remove the .tmp suffix
                $newPath = $this->replaceExtension($key);

                $this->filesystem->rename($key, $newPath);

                $paths[] = $newPath;
            }
        }

        return $paths;
    }
}
