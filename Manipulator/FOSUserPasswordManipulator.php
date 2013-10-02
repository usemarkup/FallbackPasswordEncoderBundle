<?php

namespace Markup\FallbackPasswordEncoderBundle\Manipulator;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use FOS\UserBundle\Util\UserManipulator;

/**
* A password manipulator object that uses classes from within the FOSUserBundle.
*/
class FOSUserPasswordManipulator implements PasswordManipulatorInterface
{
    /**
     * @var \Closure
     **/
    private $userManipulatorCallback;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        //using a callback here to delay access to the container in order to avoid possible circular references
        $this->userManipulatorCallback = function() use ($container) { return $container->get('fos_user.util.user_manipulator'); };
    }

    public function changePassword(UserInterface $user, $password)
    {
        $this->getUserManipulator()->changePassword($user->getUsername(), $password);
    }

    /**
     * @return UserManipulator
     **/
    private function getUserManipulator()
    {
        return $this->userManipulatorCallback->__invoke();
    }
}
