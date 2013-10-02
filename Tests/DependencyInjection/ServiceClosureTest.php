<?php

namespace Markup\FallbackPasswordEncoderBundle\Tests\DependencyInjection;

use Markup\FallbackPasswordEncoderBundle\DependencyInjection\ServiceClosure;
use Mockery as m;

/**
 * A test for a callable object that returns a service (lazily fetched).
 */
class ServiceClosureTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->serviceId = 'yay_service';
        $this->container = m::mock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->closure = new ServiceClosure($this->serviceId, $this->container);
    }

    protected function tearDown()
    {
        m::close();
    }

    public function testIsCallable()
    {
        $this->assertTrue(is_callable($this->closure));
    }

    public function testServiceFetched()
    {
        $service = function () { return false; };
        $this->container
            ->shouldReceive('get')
            ->with($this->serviceId)
            ->andReturn($service);
        $closure = $this->closure;
        $this->assertSame($service, $closure());
    }

    public function testNullReturnedWhenServiceNotAccessible()
    {
        $exception = new \Symfony\Component\DependencyInjection\Exception\RuntimeException();
        $this->container
            ->shouldReceive('get')
            ->with($this->serviceId)
            ->andThrow($exception);
        $closure = $this->closure;
        $this->assertNull($closure());
    }
}
