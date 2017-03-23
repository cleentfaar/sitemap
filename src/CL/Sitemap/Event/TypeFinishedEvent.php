<?php

declare(strict_types=1);

namespace CL\Sitemap\Event;

use CL\Sitemap\Type\TypeInterface;

class TypeFinishedEvent extends AbstractEvent
{
    /**
     * @var TypeInterface
     */
    private $type;

    /**
     * @var string[]
     */
    private $writtenPaths;

    /**
     * @param TypeInterface $type
     * @param string[]      $writtenPaths
     */
    public function __construct(TypeInterface $type, array $writtenPaths)
    {
        $this->type = $type;
        $this->writtenPaths = $writtenPaths;
    }

    /**
     * @return TypeInterface
     */
    public function getType(): TypeInterface
    {
        return $this->type;
    }

    /**
     * @return string[]
     */
    public function getWrittenPaths(): array
    {
        return $this->writtenPaths;
    }
}
