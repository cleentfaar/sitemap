<?php

declare(strict_types=1);

namespace CL\Sitemap;

use CL\Sitemap\Exception\TypeNotRegisteredException;
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
            $availableTypeNames = array_keys($this->types);

            // sort for readability
            sort($availableTypeNames);

            throw TypeNotRegisteredException::withName($name, $availableTypeNames);
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
