<?php

declare(strict_types=1);

namespace CL\Sitemap\Event;

use CL\Sitemap\Type\TypeInterface;

class TypeEntryWrittenEvent extends AbstractEvent
{
    /**
     * @var TypeInterface
     */
    private $type;

    /**
     * @param TypeInterface $type
     */
    public function __construct(TypeInterface $type)
    {
        $this->type = $type;
    }

    /**
     * @return TypeInterface
     */
    public function getType(): TypeInterface
    {
        return $this->type;
    }
}
