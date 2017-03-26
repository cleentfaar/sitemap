<?php

declare(strict_types=1);

namespace CL\Sitemap;

class Renderer
{
    /**
     * @var string
     */
    private $rootTag;

    /**
     * @var string
     */
    private $encoding;

    /**
     * @param string $rootTag
     * @param string $encoding
     */
    public function __construct(string $rootTag, string $encoding = 'UTF-8')
    {
        $this->rootTag = $rootTag;
        $this->encoding = $encoding;
    }

    /**
     * @return string
     */
    public function renderHeader(): string
    {
        return sprintf(
            "<?xml version=\"1.0\" encoding=\"%s\"?>\n<%s xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">",
            $this->encoding,
            $this->rootTag
        );
    }

    /**
     * @param Entry $entry
     *
     * @return string
     */
    public function renderEntry(Entry $entry): string
    {
        $location = $entry->getLocation()->toString();
        $changeFrequency = $entry->getChangeFrequency() ? $entry->getChangeFrequency()->toString() : null;
        $priority = $entry->getPriority() ? $entry->getPriority()->toFloat() : null;
        $lastModified = $entry->getLastModified() ? $entry->getLastModified()->toString() : null;

        $entryXml = "\n    <url><loc>$location</loc>";

        if ($changeFrequency) {
            $entryXml .= "<changefreq>$changeFrequency</changefreq>";
        }

        if ($priority) {
            $entryXml .= "<priority>$priority</priority>";
        }

        if ($lastModified) {
            $entryXml .= "<lastmod>$lastModified</lastmod>";
        }

        $entryXml .= '</url>';

        return $entryXml;
    }

    /**
     * @return string
     */
    public function renderFooter(): string
    {
        return sprintf("\n</%s>\n", $this->rootTag);
    }
}
