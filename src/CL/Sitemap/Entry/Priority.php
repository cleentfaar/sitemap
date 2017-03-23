<?php

declare(strict_types=1);

namespace CL\Sitemap\Entry;

use Assert\Assertion;

/**
 * @see https://www.sitemaps.org/protocol.html#prioritydef
 */
class Priority
{
    /**
     * @var float
     */
    private $priority;

    /**
     * @param float $priority
     */
    public function __construct(float $priority = 0.5)
    {
        Assertion::between($priority, 0, 1);

        $this->priority = $priority;
    }

    /**
     * @return float
     */
    public function toFloat(): float
    {
        return $this->priority;
    }
}
