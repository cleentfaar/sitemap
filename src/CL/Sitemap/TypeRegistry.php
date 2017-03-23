<?php

declare(strict_types=1);

namespace CL\Sitemap;

use CL\Sitemap\Type\TypeInterface;

class TypeRegistry
{
    /**
     * @var TypeInterface[]
     */
    private $types = [];

    /**
     * @param TypeInterface $type
     */
    public function register(TypeInterface $type)
    {
        $this->types[$type->getName()] = $type;
    }

    /**
     * @param string $name
     *
     * @return TypeInterface
     */
    public function get(string $name): TypeInterface
    {
        if (!array_key_exists($name, $this->types)) {
            $sitemaps = $this->types;

            // sort for readability
            ksort($sitemaps);

            throw new \InvalidArgumentException(sprintf(
                'There is no type registered with the name "%s" (available types: "%s")',
                $name,
                implode('", "', array_keys($sitemaps))
            ));
        }

        return $this->types[$name];
    }

    /**
     * @return TypeInterface[]
     */
    public function all(): array
    {
        return $this->types;
    }
}
