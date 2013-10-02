<?php

namespace Markup\FallbackPasswordEncoderBundle\Tests\Manipulator;

use Markup\FallbackPasswordEncoderBundle\Manipulator\CompositePasswordManipulator;

class CompositePasswordManipulatorTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->manipulator  = new CompositePasswordManipulator();
    }

    public function testIsManipulator()
    {
        $this->assertInstanceOf('Markup\FallbackPasswordEncoderBundle\Manipulator\PasswordManipulatorInterface', $this->manipulator);
    }

    public function testExceptionIfNoFallbackAndNoRegistered()
    {
        $this->setExpectedException('Markup\FallbackPasswordEncoderBundle\Exception\ManipulatorNotRegisteredException');
        $user = new TestUser();
        $password = 'correcthorsebatterystaple';
        $this->manipulator->changePassword($user, $password);
    }

    public function testManipulatorUsed()
    {
        $manipulator = $this->getMockBuilder('FOS\UserBundle\Util\UserManipulator')
            ->disableOriginalConstructor()
            ->getMock();
        $user = new TestUser();
        $username = 'joe@bloggs.com';
        $user->setUsername($username);
        $this->manipulator->registerManipulator(get_class($user), $manipulator);
        $password = 'correcthorsebatterystaple';
        $manipulator
            ->expects($this->once())
            ->method('changePassword')
            ->with($this->equalTo($username), $this->equalTo($password));
        $this->manipulator->changePassword($user, $password);
    }

    public function testUsesFallback()
    {
        $fallback = $this->getMock('Markup\FallbackPasswordEncoderBundle\Manipulator\PasswordManipulatorInterface');
        $user = new TestUser();
        $username = 'queen@uk.co.uk';
        $user->setUsername($username);
        $this->manipulator->registerFallback($fallback);
        $password = 'correcthorsebatterystaple';
        $fallback
            ->expects($this->once())
            ->method('changePassword')
            ->with($this->equalTo($username), $this->equalTo($password));
        $this->manipulator->changePassword($user, $password);
    }

    public function testManipulatorUsedPassedInAsClosure()
    {
        $manipulator = $this->getMockBuilder('FOS\UserBundle\Util\UserManipulator')
            ->disableOriginalConstructor()
            ->getMock();
        $username = 'joe@bloggs.com';
        $password = 'correcthorsebatterystaple';
        $manipulator
            ->expects($this->once())
            ->method('changePassword')
            ->with($this->equalTo($username), $this->equalTo($password));
        $manipulator = function () use ($manipulator) {
            return $manipulator;
        };
        $user = new TestUser();
        $user->setUsername($username);
        $this->manipulator->registerManipulator(get_class($user), $manipulator);
        $this->manipulator->changePassword($user, $password);
    }
}
