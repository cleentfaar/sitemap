<?php

declare(strict_types=1);

namespace CL\Sitemap\Bridge\Symfony\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass that can be used to register sitemap types
 * using the registry and tag of your choice.
 */
class RegisterSitemapTypesPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private $typeRegistryServiceId;

    /**
     * @var string
     */
    private $tagName;

    /**
     * @param string $typeRegistryServiceId
     * @param string $tagName
     */
    public function __construct(string $typeRegistryServiceId, string $tagName)
    {
        $this->typeRegistryServiceId = $typeRegistryServiceId;
        $this->tagName = $tagName;
    }

    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has($this->typeRegistryServiceId)) {
            return;
        }

        $definition = $container->findDefinition($this->typeRegistryServiceId);

        foreach ($container->findTaggedServiceIds($this->tagName) as $id => $tags) {
            $definition->addMethodCall('register', [new Reference($id)]);
        }
    }
}
