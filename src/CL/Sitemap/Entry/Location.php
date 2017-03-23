<?php

declare(strict_types=1);

namespace CL\Sitemap\Entry;

use Assert\Assertion;

/**
 * @see https://www.sitemaps.org/protocol.html#locdef
 */
class Location
{
    /**
     * @var string
     */
    private $value;

    /**
     * @param string $value
     */
    public function __construct(string $value)
    {
        Assertion::url($value);

        $this->value = $value;
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return $this->value;
    }
}
