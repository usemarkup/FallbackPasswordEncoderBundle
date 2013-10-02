<?php

namespace Markup\FallbackPasswordEncoderBundle\Encoder;

use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * An interface for a user aware password encoder.
 **/
interface UserAwarePasswordEncoderInterface extends PasswordEncoderInterface
{
    /**
     * Sets a user instance on this encoder.
     *
     * @param UserInterface $user
     **/
    public function setUser(UserInterface $user);
}
