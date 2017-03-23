<?php

namespace CL\Sitemap\Tests;

use CL\Sitemap\Exception\TypeNotRegisteredException;
use CL\Sitemap\Type\TypeInterface;
use CL\Sitemap\TypeRegistry;
use PHPUnit\Framework\TestCase;

class TypeRegistryTest extends TestCase
{
    /**
     * @var TypeRegistry
     */
    private $typeRegistry;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->typeRegistry = new TypeRegistry();
    }

    /**
     * @test
     */
    public function it_can_register_and_retrieve_a_type()
    {
        $type = $this->prophesize(TypeInterface::class);
        $type->getName()->willReturn($typeName = 'my_type');

        $this->typeRegistry->register($type->reveal());

        $this->assertSame($type->reveal(), $this->typeRegistry->get($typeName));
    }

    /**
     * @test
     */
    public function it_can_return_all_registered_types_by_name()
    {
        $type1 = $this->prophesize(TypeInterface::class);
        $type1->getName()->willReturn($typeName1 = 'my_type1');

        $type2 = $this->prophesize(TypeInterface::class);
        $type2->getName()->willReturn($typeName2 = 'my_type2');

        $this->typeRegistry->register($type1->reveal());
        $this->typeRegistry->register($type2->reveal());

        $this->assertSame([$typeName1 => $type1->reveal(), $typeName2 => $type2->reveal()], $this->typeRegistry->all());
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_retrieving_an_unknown_type()
    {
        $this->expectException(TypeNotRegisteredException::class);

        $this->typeRegistry->get('unknown_type');
    }
}