<?php

declare(strict_types=1);

namespace CL\Sitemap\Exception;

use RuntimeException;

class EntryLimitReachedException extends RuntimeException implements Exception
{
    /**
     * @param int    $limit
     * @param string $path
     *
     * @return EntryLimitReachedException
     */
    public static function withLimit(int $limit, string $path): self
    {
        return new self(sprintf(
            'Entry limit (%d) reached for index file (%s); you should reduce the number of entries',
            $limit,
            $path
        ));
    }
}
