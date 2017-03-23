<?php

declare(strict_types=1);

namespace CL\Sitemap;

use CL\Sitemap\Entry\ChangeFrequency;
use CL\Sitemap\Entry\Location;
use CL\Sitemap\Entry\Priority;
use DateTime;

class Entry
{
    /**
     * @var Location
     */
    private $location;

    /**
     * @var Priority|null
     */
    private $priority;

    /**
     * @var ChangeFrequency|null
     */
    private $changeFrequency;

    /**
     * @var DateTime|null
     */
    private $lastModified;

    /**
     * @param Location|null        $location
     * @param Priority|null        $priority
     * @param ChangeFrequency|null $changeFrequency
     * @param DateTime|null        $lastModified
     */
    public function __construct(
        Location $location,
        Priority $priority = null,
        ChangeFrequency $changeFrequency = null,
        DateTime $lastModified = null
    ) {
        $this->location = $location;
        $this->priority = $priority;
        $this->changeFrequency = $changeFrequency;
        $this->lastModified = $lastModified;
    }

    /**
     * @return Location
     */
    public function getLocation(): Location
    {
        return $this->location;
    }

    /**
     * @return Priority|null
     */
    public function getPriority(): ?Priority
    {
        return $this->priority;
    }

    /**
     * @return ChangeFrequency|null
     */
    public function getChangeFrequency(): ?ChangeFrequency
    {
        return $this->changeFrequency;
    }

    /**
     * @return DateTime|null
     */
    public function getLastModified(): ?DateTime
    {
        return $this->lastModified;
    }
}
