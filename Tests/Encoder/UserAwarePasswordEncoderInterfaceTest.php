<?php

namespace Markup\FallbackPasswordEncoderBundle\Tests\Encoder;

/**
* A test for a user aware password encoder interface.
*/
class UserAwarePasswordEncoderInterfaceTest extends \PHPUnit_Framework_TestCase
{
    public function testExpectedPublicMethods()
    {
        $expectedPublicMethods = array(
            'encodePassword',
            'isPasswordValid',
            'setUser',
            );
        $actualPublicMethods = array();
        $refl = new \ReflectionClass($this->getInterfaceUnderTest());
        foreach ($refl->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $actualPublicMethods[] = $method->name;
        }
        sort($expectedPublicMethods);
        sort($actualPublicMethods);
        $this->assertEquals($expectedPublicMethods, $actualPublicMethods);
    }

    public function testIsPasswordEncoder()
    {
        $refl = new \ReflectionClass($this->getInterfaceUnderTest());
        $this->assertTrue($refl->implementsInterface('Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface'));
    }

    private function getInterfaceUnderTest()
    {
        return 'Markup\FallbackPasswordEncoderBundle\Encoder\UserAwarePasswordEncoderInterface';
    }
}
