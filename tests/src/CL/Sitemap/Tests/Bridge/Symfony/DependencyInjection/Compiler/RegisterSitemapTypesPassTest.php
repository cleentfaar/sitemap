<?php

declare(strict_types=1);

namespace CL\Sitemap\Tests\DependencyInjection\Compiler;

use CL\Bundle\MailerBundle\DependencyInjection\Compiler\RegisterMailerTypesPass;
use CL\Sitemap\Bridge\Symfony\DependencyInjection\Compiler\RegisterSitemapTypesPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class RegisterSitemapTypesPassTest extends AbstractCompilerPassTestCase
{
    const REGISTRY_ID = 'my_type_registry_service_id';
    const TYPE_ID = 'my_type_service_id';
    const TAG_NAME = 'my_type_tag_name';

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $collectingService = new Definition();
        $this->setDefinition(self::REGISTRY_ID, $collectingService);

        $collectedService = new Definition();
        $collectedService->addTag(self::TAG_NAME);
        $this->setDefinition(self::TYPE_ID, $collectedService);
    }

    /**
     * @test
     */
    public function if_compiler_pass_collects_services_by_adding_method_calls_these_will_exist()
    {
        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            self::REGISTRY_ID,
            'register',
            [
                new Reference(self::TYPE_ID),
            ]
        );
    }

    /**
     * @inheritdoc
     */
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RegisterSitemapTypesPass(self::REGISTRY_ID, self::TAG_NAME));
    }
}
