<?php

namespace Markup\FallbackPasswordEncoderBundle\Encoder;

use Markup\FallbackPasswordEncoderBundle\Manipulator\PasswordManipulatorInterface;
use Symfony\Component\Security\Core\Encoder\BasePasswordEncoder;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
* A password encoder that can wrap legacy password encodings and upgrade passwords seamlessly to a primary encoding if they are found to be of a legacy encoding.
*/
class FallbackPasswordEncoder extends BasePasswordEncoder implements UserAwarePasswordEncoderInterface
{
    /**
     * The primary encoder that should be used for new passwords.
     *
     * @var PasswordEncoderInterface
     **/
    private $primaryEncoder;

    /**
     * The collection of fallback legacy encoders that can be used to check existing passwords.
     *
     * @var PasswordEncoderInterface[]
     **/
    private $fallbackLegacyEncoders;

    /**
     * @var PasswordManipulatorInterface
     **/
    private $passwordManipulator;

    /**
     * The user instance this encoder is being used on.
     *
     * @var UserInterface
     **/
    private $user = null;

    /**
     * @param PasswordEncoderInterface     $primary_encoder
     * @param PasswordEncoderInterface[]   $fallback_legacy_encoders
     * @param PasswordManipulatorInterface $password_manipulator
     **/
    public function __construct(PasswordEncoderInterface $primary_encoder, $fallback_legacy_encoders, PasswordManipulatorInterface $password_manipulator)
    {
        $this->primaryEncoder = $primary_encoder;
        $this->fallbackLegacyEncoders = $fallback_legacy_encoders;
        $this->passwordManipulator = $password_manipulator;
    }

    public function setUser(UserInterface $user)
    {
        $this->user = $user;
    }

    public function encodePassword($raw, $salt)
    {
        if ($this->isPasswordTooLong($raw)) {
            throw new BadCredentialsException('Invalid password.');
        }

        return $this->getPrimaryEncoder()->encodePassword($raw, $salt);
    }

    public function isPasswordValid($encoded, $raw, $salt)
    {
        if ($this->isPasswordTooLong($raw)) {
            return false;
        }
        if ($this->getPrimaryEncoder()->isPasswordValid($encoded, $raw, $salt)) {
            return true;
        }
        foreach ($this->getFallbackLegacyEncoders() as $legacyEncoder) {
            if ($legacyEncoder->isPasswordValid($encoded, $raw, $salt)) {
                //save a new encoding of the same password using the primary encoder
                $this->getPasswordManipulator()->changePassword($this->getUser(), $raw);

                return true;
            }
        }

        return false;
    }

    /**
     * @return PasswordEncoderInterface
     **/
    private function getPrimaryEncoder()
    {
        return $this->primaryEncoder;
    }

    /**
     * @return PasswordEncoderInterface[]
     **/
    private function getFallbackLegacyEncoders()
    {
        return $this->fallbackLegacyEncoders;
    }

    /**
     * @return PasswordManipulatorInterface
     **/
    private function getPasswordManipulator()
    {
        return $this->passwordManipulator;
    }

    /**
     * Gets the user set on this encoder.  If no user set, throws a \LogicException.
     *
     * @return UserInterface
     * @throws \LogicException if user is not set
     **/
    private function getUser()
    {
        if (null === $this->user) {
            throw new \LogicException(sprintf('Tried to call %s on a user aware password encoder (instance of "%s") that did not have a user set on it using setUser().', __METHOD__, get_class($this)));
        }

        return $this->user;
    }
}
