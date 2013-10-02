<?php

namespace Markup\FallbackPasswordEncoderBundle\Tests\Manipulator;

use Markup\FallbackPasswordEncoderBundle\Manipulator\FOSUserPasswordManipulator;

/**
* A test for a password manipulator object that uses classes from within the FOSUserBundle.
*/
class FOSUserPasswordManipulatorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!class_exists('FOS\UserBundle\Util\UserManipulator')) {
            $this->markTestSkipped('FOSUserBundle not included into this codebase.');
        }
        $this->userManipulator = $this->getMockBuilder('FOS\UserBundle\Util\UserManipulator')
            ->disableOriginalConstructor()
            ->getMock();
        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->container
            ->expects($this->any())
            ->method('get')
            ->with($this->equalTo('fos_user.util.user_manipulator'))
            ->will($this->returnValue($this->userManipulator));
        $this->manipulator = new FOSUserPasswordManipulator($this->container);
    }

    public function testIsPasswordManipulator()
    {
        $this->assertTrue($this->manipulator instanceof \Markup\FallbackPasswordEncoderBundle\Manipulator\PasswordManipulatorInterface);
    }

    public function testChangePassword()
    {
        $user = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');
        $username = 'joebloggs';
        $user
            ->expects($this->any())
            ->method('getUsername')
            ->will($this->returnValue($username));
        $password = 'iamapasswordhash';
        $this->userManipulator
            ->expects($this->once())
            ->method('changePassword')
            ->with($this->equalTo($username), $this->equalTo($password));
        $this->manipulator->changePassword($user, $password);
    }
}
