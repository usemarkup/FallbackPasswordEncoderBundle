<?php

namespace Markup\FallbackPasswordEncoderBundle\Tests\Encoder;

use Markup\FallbackPasswordEncoderBundle\Encoder\EncoderFactory;
use Symfony\Component\Security\Tests\Core\Encoder\EncoderFactoryTest as BaseEncoderFactoryTest;

/**
* A test for an encoder factory that allows the fetching of user-aware encoders.
*/
class EncoderFactoryTest extends BaseEncoderFactoryTest
{
    public function setUp()
    {
        $userClassKey = 'Symfony\Component\Security\Core\User\UserInterface';
        $this->user = $this->getMock($userClassKey);
        $this->encoder = $this->getMock('Markup\FallbackPasswordEncoderBundle\Encoder\UserAwarePasswordEncoderInterface');
        $this->factory = new EncoderFactory(array($userClassKey => $this->encoder));
    }

    public function testIsEncoderFactory()
    {
        $this->assertTrue($this->factory instanceof \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface);
    }

    public function testGetEncoderWithUserAwarePasswordEncoder()
    {
        $this->encoder
            ->expects($this->once())
            ->method('setUser')
            ->with($this->equalTo($this->user));
        $this->factory->getEncoder($this->user);
    }

    public function testGetEncoderWithUserAsString()
    {
        $user = get_class($this->user);
        $this->assertSame($this->encoder, $this->factory->getEncoder($user));
    }
}
