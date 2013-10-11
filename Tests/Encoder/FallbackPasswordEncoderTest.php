<?php

namespace Markup\FallbackPasswordEncoderBundle\Tests\Encoder;

use Markup\FallbackPasswordEncoderBundle\Encoder\FallbackPasswordEncoder;

/**
* A test for a password encoder that can wrap legacy password encodings and upgrade passwords seamlessly to a primary encoding.
*/
class FallbackPasswordEncoderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->primaryEncoder = $this->getMock('Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface');
        $this->legacyEncoder = $this->getMock('Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface');
        $this->passwordManipulator = $this->getMock('Markup\FallbackPasswordEncoderBundle\Manipulator\PasswordManipulatorInterface');
        $this->user = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');
        $this->encoder = new FallbackPasswordEncoder($this->primaryEncoder, array($this->legacyEncoder), $this->passwordManipulator);
        $this->encoder->setUser($this->user);
    }

    public function testIsUserAwarePasswordEncoder()
    {
        $this->assertInstanceOf('Markup\FallbackPasswordEncoderBundle\Encoder\UserAwarePasswordEncoderInterface', $this->encoder);
    }

    public function testExtendsBasePasswordEncoder()
    {
        $this->assertInstanceOf('Symfony\Component\Security\Core\Encoder\BasePasswordEncoder', $this->encoder);
    }

    public function testEncodePasswordUsesPrimaryEncoder()
    {
        $raw = 'iamapassword';
        $salt = 'mmm,salty';
        $this->primaryEncoder
            ->expects($this->once())
            ->method('encodePassword')
            ->with($this->equalTo($raw), $this->equalTo($salt));
        $this->encoder->encodePassword($raw, $salt);
    }

    public function testValidationCheckTrueOnPrimaryPreventsCheckWithLegacy()
    {
        $this->primaryEncoder
            ->expects($this->any())
            ->method('isPasswordValid')
            ->will($this->returnValue(true));
        $this->legacyEncoder
            ->expects($this->never())
            ->method('isPasswordValid');
        $this->assertTrue($this->encoder->isPasswordValid('kjghkgjhekgjh', 'iamapassword', 'mmm,salty'));
    }

    public function testValidationCheckFalseOnPrimaryAndLegacy()
    {
        $this->primaryEncoder
            ->expects($this->once())
            ->method('isPasswordValid')
            ->will($this->returnValue(false));
        $this->legacyEncoder
            ->expects($this->once())
            ->method('isPasswordValid')
            ->will($this->returnValue(false));
        $this->assertFalse($this->encoder->isPasswordValid('kjghkgjhekgjh', 'iamapassword', 'mmm,salty'));
    }

    public function testValidationCheckFalseForPrimaryTrueForLegacyChangesPassword()
    {
        $password = 'iamapassword';
        $this->primaryEncoder
            ->expects($this->once())
            ->method('isPasswordValid')
            ->will($this->returnValue(false));
        $this->legacyEncoder
            ->expects($this->once())
            ->method('isPasswordValid')
            ->will($this->returnValue(true));
        $this->passwordManipulator
            ->expects($this->once())
            ->method('changePassword')
            ->with($this->equalTo($this->user), $this->equalTo($password));
        $this->assertTrue($this->encoder->isPasswordValid('dfjghdkfjh', 'iamapassword', 'mmm,salty'));
    }

    public function testEncodePasswordGreaterThan4096ThrowsBadCredentialsException()
    {
        $longPassword = str_repeat('a', 4097);
        $salt = 'mmm,salty';
        $this->setExpectedException('Symfony\Component\Security\Core\Exception\BadCredentialsException');
        $this->encoder->encodePassword($longPassword, $salt);
    }

    public function testPasswordNotValidIfTooLong()
    {
        $this->primaryEncoder
            ->expects($this->any())
            ->method('isPasswordValid')
            ->will($this->returnValue(true));
        $raw = str_repeat('a', 4097);
        $this->assertFalse($this->encoder->isPasswordValid('ksjkdjfgh', $raw, 'salt'));
    }
}
