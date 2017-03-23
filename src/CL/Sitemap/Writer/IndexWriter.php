<?php

namespace CL\Sitemap\Writer;

use CL\Sitemap\Type\TypeInterface;
use Gaufrette\Filesystem;
use Gaufrette\Stream;
use Symfony\Component\HttpFoundation\File\File;

class IndexWriter
{
    const DEFAULT_INDEX_FILENAME = 'index';

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $indexFilename;

    /**
     * @var Stream|null
     */
    private $stream;

    /**
     * @param Filesystem  $filesystem
     * @param string|null $indexFilename
     */
    public function __construct(Filesystem $filesystem, string $indexFilename = null)
    {
        $this->filesystem = $filesystem;
        $this->indexFilename = $indexFilename ?: self::DEFAULT_INDEX_FILENAME;
    }

    /**
     * @param TypeInterface $type
     * @param string                  $path
     */
    public function write(TypeInterface $type, $path)
    {
        $pathToIndex = $this->getTemporaryPathToIndex();

        if (!isset($this->stream)) {
            $this->stream = fopen($pathToIndex, 'w+');
        }

        $fileModified = '@' . filemtime($path);
        $fileName = basename(rtrim($path, '.tmp'));
        $content = $this->templating->render('sitemap/index/sitemap.xml.twig', [
            'name' => $type->getName(),
            'file' => $fileName,
            'last_modified' => new \DateTime($fileModified),
        ]);

        fwrite($this->stream, $content);
    }

    /**
     * @return bool
     */
    public function finish()
    {
        if (!$this->stream) {
            return false;
        }

        fclose($this->stream);

        $tempPathToIndex = $this->getTemporaryPathToIndex();

        if (!file_exists($tempPathToIndex)) {
            // no writes done, stop here
            return false;
        }

        $content = $this->templating->render('sitemap/index/header.xml.twig');
        $content .= file_get_contents($tempPathToIndex);
        $content .= $this->templating->render('sitemap/index/footer.xml.twig');

        file_put_contents($tempPathToIndex, $content);

        $pathToIndex = $this->getPathToIndex();

        $file = new File($tempPathToIndex, false);
        $file->move(dirname($pathToIndex), basename($pathToIndex));

        $this->stream = null;

        return true;
    }

    /**
     * @return string
     */
    public function getPathToIndex()
    {
        return sprintf('%s.xml', $this->indexFilename);
    }

    /**
     * @return string
     */
    private function getTemporaryPathToIndex()
    {
        return sprintf('%s.xml.tmp', $this->indexFilename);
    }
}
