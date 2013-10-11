<?php

namespace Markup\FallbackPasswordEncoderBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class MarkupFallbackPasswordEncoderExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        //we need to add in manipulators that are defined in the configuration, and probably simplify the stuff below a lot

        $manipulatorConfig = $config['manipulators'];
        $passwordManipulatorId = 'markup_fallback_password_encoder.manipulator';
        $passwordManipulator = $container->getDefinition($passwordManipulatorId);
        foreach ($manipulatorConfig as $userClass => $manipulatorId) {
            //make a closure service definition
            $manipulator = new DefinitionDecorator('markup_fallback_password_encoder.service_closure');
            $manipulator->setArguments(array(
                $manipulatorId,
                new Reference('service_container')
            ));
            $closureServiceId = $manipulatorId.'.closure';
            $container->setDefinition($closureServiceId, $manipulator);
            $passwordManipulator->addMethodCall('registerManipulator', array($userClass, new Reference($closureServiceId)));
        }

        $fallbackEncoderDefinition = new Definition();
        $fallbackEncoderDefinition
            ->setClass('Markup\FallbackPasswordEncoderBundle\Encoder\FallbackPasswordEncoder')
            ->setArguments(array(
                new Reference($config['encoders']['primary']['id']),
                array_map(function($fallback) { return new Reference($fallback['id']); }, $config['encoders']['fallbacks']),
                new Reference($passwordManipulatorId),
                )
            );
        $container->setDefinition('markup_fallback_password_encoder', $fallbackEncoderDefinition);

        //overwrite security.encoder_factory.generic.class param
        $container->setParameter('security.encoder_factory.generic.class', 'Markup\FallbackPasswordEncoderBundle\Encoder\EncoderFactory');
    }
}
