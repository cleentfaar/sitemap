<?php

declare(strict_types=1);

namespace CL\Sitemap\Type;

use CL\Sitemap\Entry;
use Generator;

interface TypeInterface
{
    /**
     * @return Generator|Entry[]
     */
    public function iterate(): Generator;

    /**
     * @return string
     */
    public function getName(): string;
}
