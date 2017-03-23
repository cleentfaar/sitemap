<?php

declare(strict_types=1);

namespace CL\Sitemap\Writer;

use CL\Sitemap\Entry;
use CL\Sitemap\Type\TypeInterface;
use Gaufrette\Filesystem;
use Gaufrette\Stream;
use Gaufrette\StreamMode;
use RuntimeException;

class TypeWriter
{
    const DEFAULT_MAX_NUMBER_OF_URLS = 49000; // 50000 URLs is the limit, using 49000 to be sure
    const DEFAULT_MAX_FILESIZE = 9; // 10 megabytes is the limit, using 9 to be sure
    const TEMPORARY_EXTENSION = 'xml.tmp';
    const PERMANENT_EXTENSION = 'xml';

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
     * @param Filesystem    $filesystem
     * @param TypeInterface $type
     * @param int           $maxUrlLimit
     * @param int           $maxSizeLimit
     */
    public function __construct(
        Filesystem $filesystem,
        TypeInterface $type,
        int $maxUrlLimit = self::DEFAULT_MAX_NUMBER_OF_URLS,
        int $maxSizeLimit = self::DEFAULT_MAX_FILESIZE
    ) {
        $this->rootDir = $type->getName();

        if (!$filesystem->has($this->rootDir)) {
            $filesystem->write($this->rootDir, '');
        }

        $this->filesystem = $filesystem;
        $this->type = $type;
        $this->path = $this->getPath(0);
    }

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
     * @param Entry $entry
     */
    public function write(Entry $entry)
    {
        if (!$this->started) {
            throw new RuntimeException('You must call start() first');
        }

        if ($this->urlLimitReached() || $this->sizeLimitReached()) {
            $this->rotate();
        }

        $this->stream->write($this->renderEntry($entry));

        if (!in_array($this->path, $this->writtenPaths)) {
            $this->writtenPaths[] = $this->path;
        }

        if (!isset($this->urlsAdded[$this->path])) {
            $this->urlsAdded[$this->path] = 0;
        }

        ++$this->urlsAdded[$this->path];
    }

    private function rotate()
    {
        $this->stream->close();

        ++$this->partNumber;

        $this->path = $this->getPath($this->partNumber);

        $this->stream = $this->createStream();
    }

    public function finish()
    {
        if ($this->finished) {
            throw new RuntimeException('You must call start() first');
        }

        if ($this->finished) {
            throw new RuntimeException('Already finished, you need to call start() again');
        }

        $this->stream->close();

        // add headers and footers to all written (tmp) files
        foreach ($this->writtenPaths as $path) {
            $newContent = '';
            $newContent .= $this->renderHeader();
            $newContent .= $this->filesystem->read($path);
            $newContent .= $this->renderFooter();

            $this->filesystem->write($path, $newContent, true);
        }

        $this->writtenPaths = [];

        // TODO remove when vendor's bugfix gets released (https://github.com/KnpLabs/Gaufrette/pull/316)
        $this->filesystem->clearFileRegister();

        // remove all current xml files for this type
        foreach ($this->findPartPaths() as $key) {
            if ($this->pathHasExtension($key, self::PERMANENT_EXTENSION)) {
                $this->filesystem->delete($key);
            }
        }

        // TODO remove when vendor's bugfix gets released (https://github.com/KnpLabs/Gaufrette/pull/316)
        //$this->filesystem->clearFileRegister();

        // rename all newly written .xml.tmp files to .xml
        foreach ($this->findPartPaths() as $key) {
            if ($this->pathHasExtension($key, self::TEMPORARY_EXTENSION)) {
                // remove the .tmp suffix
                $newPath = $this->replaceExtension($key);

                $this->filesystem->rename($key, $newPath);
            }
        }

        // TODO remove when vendor's bugfix gets released (https://github.com/KnpLabs/Gaufrette/pull/316)
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
     * @return string
     */
    private function renderHeader(): string
    {
        return <<<EOL
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

EOL;
    }

    /**
     * @param Entry $entry
     *
     * @return string
     */
    private function renderEntry(Entry $entry): string
    {
        $location = $entry->getLocation()->toString();
        $changeFrequency = $entry->getChangeFrequency() ? $entry->getChangeFrequency()->toString() : null;
        $priority = $entry->getPriority() ? $entry->getPriority()->toFloat() : null;
        $lastModified = $entry->getLastModified() ? $entry->getLastModified()->format('Y-m-d') : null;

        $entryXml = <<<EOL
<url>
    <loc>$location</loc>
EOL;

        if ($changeFrequency) {
            $entryXml .= <<<EOL
    <changefreq>$changeFrequency</changefreq>
EOL;
        }

        if ($priority) {
            $entryXml .= <<<EOL
    <priority>$priority</priority>
EOL;
        }

        if ($lastModified) {
            $entryXml .= <<<EOL
    <lastmod>$lastModified</lastmod>
EOL;
        }

        $entryXml .= <<<EOL
</url>
EOL;

        return $entryXml;
    }

    /**
     * @return string
     */
    private function renderFooter(): string
    {
        return <<<EOL
</urlset>

EOL;
    }

    /**
     * @return bool
     */
    private function urlLimitReached(): bool
    {
        if (!isset($this->urlsAdded[$this->path])) {
            return false;
        }

        return $this->urlsAdded[$this->path] >= self::DEFAULT_MAX_NUMBER_OF_URLS;
    }

    /**
     * @return bool
     */
    private function sizeLimitReached(): bool
    {
        $size = $this->stream->stat()['size'];

        return $size / (1024 * 1024) >= self::DEFAULT_MAX_FILESIZE;
    }

    /**
     * @return string[]
     */
    private function findPartPaths(): array
    {
        return $this->filesystem->listKeys($this->rootDir.'/')['keys'];
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
}