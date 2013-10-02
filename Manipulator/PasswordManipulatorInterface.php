<?php

namespace Markup\FallbackPasswordEncoderBundle\Manipulator;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * An interface for an object that can modify the password on a user.
 **/
interface PasswordManipulatorInterface
{
    /**
     * Changes the password on the provided user with the provided password.  Password should be raw, not hashed.
     *
     * @param UserInterface $user
     * @param string        $password
     **/
    public function changePassword(UserInterface $user, $password);
}
