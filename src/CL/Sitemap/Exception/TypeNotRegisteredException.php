<?php

declare(strict_types=1);

namespace CL\Sitemap\Exception;

use RuntimeException;

class TypeNotRegisteredException extends RuntimeException implements Exception
{
    /**
     * @param string   $name
     * @param string[] $availableTypeNames
     *
     * @return self
     */
    public static function withName(string $name, array $availableTypeNames): self
    {
        return new self(sprintf(
            'There is no type registered with that name: "%s" (available types: "%s")',
            $name,
            implode('", "', $availableTypeNames)
        ));
    }
}
