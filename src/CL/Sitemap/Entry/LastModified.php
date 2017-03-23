<?php

declare(strict_types=1);

namespace CL\Sitemap\Entry;

use DateTime;

/**
 * @see https://www.sitemaps.org/protocol.html#lastmoddef
 */
class LastModified
{
    /**
     * @var DateTime
     */
    private $date;

    /**
     * @param DateTime $date
     */
    public function __construct(DateTime $date)
    {
        $this->date = $date;
    }

    /**
     * @param int $timestamp
     *
     * @return LastModified
     */
    public static function fromUnixTimestamp(int $timestamp)
    {
        return new self(DateTime::createFromFormat('U', (string) $timestamp));
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return $this->date->format('Y-m-d');
    }
}
