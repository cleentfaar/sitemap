<?php

declare(strict_types=1);

namespace CL\Sitemap\Exception;

use RuntimeException;

class SizeLimitReachedException extends RuntimeException implements Exception
{
    /**
     * @param float  $limit Size limit in megabytes
     * @param string $path  Path to the file that has become too large
     *
     * @return self
     */
    public static function withLimit(float $limit, string $path): self
    {
        return new self(sprintf(
            'Size limit (%fMB) reached for index file (%s); you should reduce the number of entries',
            $limit,
            $path
        ));
    }
}
