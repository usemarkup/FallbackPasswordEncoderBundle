<?php

namespace Markup\FallbackPasswordEncoderBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ExceptionInterface as DependencyInjectionException;

/**
 * A closure to provide services lazily (container-aware).
 */
class ServiceClosure
{
    /**
     * An ID for a service to fetch.
     *
     * @var string
     **/
    private $serviceId;

    /**
     * @var ContainerInterface
     **/
    private $container;

    /**
     * @param string             $serviceId
     * @param ContainerInterface $container
     **/
    public function __construct($serviceId, ContainerInterface $container)
    {
        $this->serviceId = $serviceId;
        $this->container = $container;
    }

    public function __invoke()
    {
        try {
            $service = $this->container->get($this->serviceId);
        } catch (DependencyInjectionException $e) {
            return null;
        }

        return $service;
    }
}
