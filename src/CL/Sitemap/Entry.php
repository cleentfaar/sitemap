<?php

declare(strict_types=1);

namespace CL\Sitemap;

use CL\Sitemap\Entry\ChangeFrequency;
use CL\Sitemap\Entry\LastModified;
use CL\Sitemap\Entry\Location;
use CL\Sitemap\Entry\Priority;

class Entry
{
    /**
     * @var Location
     */
    private $location;

    /**
     * @var ChangeFrequency|null
     */
    private $changeFrequency;

    /**
     * @var LastModified|null
     */
    private $lastModified;

    /**
     * @var Priority|null
     */
    private $priority;

    /**
     * @param Location|null        $location
     * @param ChangeFrequency|null $changeFrequency
     * @param LastModified|null    $lastModified
     * @param Priority|null        $priority
     */
    public function __construct(
        Location $location,
        ChangeFrequency $changeFrequency = null,
        LastModified $lastModified = null,
        Priority $priority = null
    ) {
        $this->location = $location;
        $this->changeFrequency = $changeFrequency;
        $this->lastModified = $lastModified;
        $this->priority = $priority;
    }

    /**
     * @return Location
     */
    public function getLocation(): Location
    {
        return $this->location;
    }

    /**
     * @return ChangeFrequency|null
     */
    public function getChangeFrequency(): ?ChangeFrequency
    {
        return $this->changeFrequency;
    }

    /**
     * @return LastModified|null
     */
    public function getLastModified(): ?LastModified
    {
        return $this->lastModified;
    }

    /**
     * @return Priority|null
     */
    public function getPriority(): ?Priority
    {
        return $this->priority;
    }
}
