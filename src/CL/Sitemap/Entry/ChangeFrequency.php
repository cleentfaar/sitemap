<?php

declare(strict_types=1);

namespace CL\Sitemap\Entry;

use Assert\Assertion;
use ReflectionClass;

/**
 * @see https://www.sitemaps.org/protocol.html#changefreqdef
 */
class ChangeFrequency
{
    const FREQUENCY_ALWAYS = 'always';
    const FREQUENCY_HOURLY = 'hourly';
    const FREQUENCY_DAILY = 'daily';
    const FREQUENCY_WEEKLY = 'weekly';
    const FREQUENCY_MONTHLY = 'monthly';
    const FREQUENCY_YEARLY = 'yearly';
    const FREQUENCY_NEVER = 'never';

    /**
     * @var string
     */
    private $frequency;

    /**
     * @param string $frequency
     */
    public function __construct(string $frequency)
    {
        $reflector = new ReflectionClass(__CLASS__);
        $availableFrequencies = [];
        $prefix = 'FREQUENCY_';

        foreach ($reflector->getConstants() as $constant => $value) {
            if (strpos($constant, $prefix) === 0) {
                $availableFrequencies[] = $value;
            }
        }

        Assertion::inArray($frequency, $availableFrequencies);

        $this->frequency = $frequency;
    }

    /**
     * @return self
     */
    public static function always(): self
    {
        return new self(self::FREQUENCY_ALWAYS);
    }

    /**
     * @return self
     */
    public static function hourly(): self
    {
        return new self(self::FREQUENCY_HOURLY);
    }

    /**
     * @return self
     */
    public static function daily(): self
    {
        return new self(self::FREQUENCY_DAILY);
    }

    /**
     * @return self
     */
    public static function weekly(): self
    {
        return new self(self::FREQUENCY_WEEKLY);
    }

    /**
     * @return self
     */
    public static function monthly(): self
    {
        return new self(self::FREQUENCY_MONTHLY);
    }

    /**
     * @return self
     */
    public static function yearly(): self
    {
        return new self(self::FREQUENCY_YEARLY);
    }

    /**
     * @return self
     */
    public static function never(): self
    {
        return new self(self::FREQUENCY_NEVER);
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return $this->frequency;
    }
}
