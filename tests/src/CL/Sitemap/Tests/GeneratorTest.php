<?php

declare(strict_types=1);

namespace CL\Sitemap\Tests;

use CL\Sitemap\Entry;
use CL\Sitemap\Event\IndexEntryWrittenEvent;
use CL\Sitemap\Event\IndexFinishedEvent;
use CL\Sitemap\Event\IndexStartedEvent;
use CL\Sitemap\Event\TypeEntryWrittenEvent;
use CL\Sitemap\Event\TypeFinishedEvent;
use CL\Sitemap\Event\TypeStartedEvent;
use CL\Sitemap\Generator;
use CL\Sitemap\SimpleIndexEntryResolver;
use CL\Sitemap\Type\TypeInterface;
use CL\Sitemap\TypeRegistry;
use Gaufrette\Adapter\InMemory;
use Gaufrette\Filesystem;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GeneratorTest extends TestCase
{
    const TYPE_NAME = 'my_type';

    /**
     * @var Generator
     */
    private $generator;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var TypeRegistry
     */
    private $typeRegistry;

    /**
     * @var ObjectProphecy
     */
    private $eventDispatcher;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $indexEntryResolver = new SimpleIndexEntryResolver('https://acme.com/sitemaps');

        $this->filesystem = new Filesystem(new InMemory());
        $this->typeRegistry = new TypeRegistry();
        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->generator = new Generator(
            $this->filesystem,
            $this->typeRegistry,
            $indexEntryResolver,
            $this->eventDispatcher->reveal()
        );
    }

    /**
     * @test
     */
    public function it_can_generate_index_and_type_files_and_dispatch_expected_events()
    {
        $type = $this->prophesize(TypeInterface::class);
        $type->__call('getName', [])->willReturn(self::TYPE_NAME);
        $type->__call('iterate', [])->will(function () {
            yield new Entry(
                new Entry\Location('https://www.acme.com/product/1234')
            );
        });

        $this->typeRegistry->register($type->reveal());

        $this->generator->generate();

        $this->eventDispatcher->__call('dispatch', [Generator::EVENT_INDEX_STARTED, Argument::type(IndexStartedEvent::class)])->shouldBeCalledTimes(1);
        $this->eventDispatcher->__call('dispatch', [Generator::EVENT_TYPE_STARTED, Argument::type(TypeStartedEvent::class)])->shouldBeCalledTimes(1);
        $this->eventDispatcher->__call('dispatch', [Generator::EVENT_TYPE_ENTRY_WRITTEN, Argument::type(TypeEntryWrittenEvent::class)])->shouldBeCalledTimes(1);
        $this->eventDispatcher->__call('dispatch', [Generator::EVENT_TYPE_FINISHED, Argument::type(TypeFinishedEvent::class)])->shouldBeCalledTimes(1);
        $this->eventDispatcher->__call('dispatch', [Generator::EVENT_INDEX_ENTRY_WRITTEN, Argument::type(IndexEntryWrittenEvent::class)])->shouldBeCalledTimes(1);
        $this->eventDispatcher->__call('dispatch', [Generator::EVENT_INDEX_FINISHED, Argument::type(IndexFinishedEvent::class)])->shouldBeCalledTimes(1);
    }
}
