<?php

namespace CL\Sitemap\Event;

class IndexStartedEvent extends AbstractEvent
{
    /**
     * @var string[]
     */
    private $typeNames;

    /**
     * @param string[] $typeNames
     */
    public function __construct(array $typeNames)
    {
        $this->typeNames = $typeNames;
    }

    /**
     * @return string[]
     */
    public function getTypeNames(): array
    {
        return $this->typeNames;
    }
}