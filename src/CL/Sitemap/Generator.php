<?php

declare(strict_types=1);

namespace CL\Sitemap;

use CL\Sitemap\Event\IndexEntryWrittenEvent;
use CL\Sitemap\Event\IndexFinishedEvent;
use CL\Sitemap\Event\IndexStartedEvent;
use CL\Sitemap\Event\TypeEntryWrittenEvent;
use CL\Sitemap\Event\TypeFinishedEvent;
use CL\Sitemap\Event\TypeStartedEvent;
use CL\Sitemap\Writer\IndexWriter;
use CL\Sitemap\Writer\TypeWriter;
use Closure;
use Gaufrette\Filesystem;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Convenience wrapper for the complete sitemap functionality
 * Can be hooked into by injecting your own event dispatcher.
 */
class Generator
{
    const EVENT_INDEX_STARTED = 'INDEX_STARTED';
    const EVENT_TYPE_STARTED = 'TYPE_STARTED';
    const EVENT_TYPE_ENTRY_WRITTEN = 'TYPE_ENTRY_WRITTEN';
    const EVENT_TYPE_FINISHED = 'TYPE_FINISHED';
    const EVENT_INDEX_ENTRY_WRITTEN = 'INDEX_ENTRY_WRITTEN';
    const EVENT_INDEX_FINISHED = 'INDEX_FINISHED';

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var TypeRegistry
     */
    private $typeRegistry;

    /**
     * @var Closure
     */
    private $indexEntryResolver;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param Filesystem                    $filesystem
     * @param TypeRegistry                  $typeRegistry
     * @param IndexEntryResolverInterface   $indexEntryResolver
     * @param EventDispatcherInterface|null $eventDispatcher
     */
    public function __construct(
        Filesystem $filesystem,
        TypeRegistry $typeRegistry,
        IndexEntryResolverInterface $indexEntryResolver,
        EventDispatcherInterface $eventDispatcher = null
    ) {
        $this->filesystem = $filesystem;
        $this->typeRegistry = $typeRegistry;
        $this->indexEntryResolver = $indexEntryResolver;
        $this->eventDispatcher = $eventDispatcher ?: new EventDispatcher();
    }

    public function generate()
    {
        $types = $this->typeRegistry->all();

        $indexWriter = new IndexWriter($this->filesystem);
        $indexWriter->start();

        $this->eventDispatcher->dispatch(self::EVENT_INDEX_STARTED, new IndexStartedEvent(array_keys($types)));

        foreach ($types as $type) {
            $typeWriter = new TypeWriter($this->filesystem, $type);
            $typeWriter->start();

            $this->eventDispatcher->dispatch(self::EVENT_TYPE_STARTED, new TypeStartedEvent($type));

            foreach ($type->iterate() as $entry) {
                $typeWriter->write($entry);

                $this->eventDispatcher->dispatch(self::EVENT_TYPE_ENTRY_WRITTEN, new TypeEntryWrittenEvent($type));
            }

            $paths = $typeWriter->finish();

            $this->eventDispatcher->dispatch(self::EVENT_TYPE_FINISHED, new TypeFinishedEvent($type, $paths));

            foreach ($paths as $path) {
                $typeEntry = $this->indexEntryResolver->resolve($path, $this->filesystem->get($path)->getMtime());

                $indexWriter->write($typeEntry);

                $this->eventDispatcher->dispatch(self::EVENT_INDEX_ENTRY_WRITTEN, new IndexEntryWrittenEvent());
            }
        }

        $indexWriter->finish();

        $this->eventDispatcher->dispatch(self::EVENT_INDEX_FINISHED, new IndexFinishedEvent());
    }
}
