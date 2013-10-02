<?php

namespace Markup\FallbackPasswordEncoderBundle\Manipulator;

use Markup\FallbackPasswordEncoderBundle\Exception\ManipulatorNotRegisteredException;
use Symfony\Component\Security\Core\User\UserInterface;
use FOS\UserBundle\Util\UserManipulator;

class CompositePasswordManipulator implements PasswordManipulatorInterface
{
    /**
     * @var UserManipulator[]
     */
    private $manipulators;

    /**
     * @var PasswordManipulatorInterface|null
     */
    private $fallback;

    public function __construct()
    {
        $this->manipulators = array();
    }

    /**
     * {@inheritdoc}
     **/
    public function changePassword(UserInterface $user, $password)
    {
        if (!isset($this->manipulators[get_class($user)])) {
            if (null === $this->fallback) {
                throw new ManipulatorNotRegisteredException(sprintf('No manipulator was available for the user class "%s".', get_class($user)));
            }

            $this->fallback->changePassword($user, $password);

            return;
        }

        $manipulator = $this->manipulators[get_class($user)];
        if (is_callable($manipulator)) {
            $manipulator = call_user_func($manipulator);
        }
        $manipulator->changePassword($user->getUsername(), $password);
    }

    /**
     * Registers a manipulator for the given user class.
     *
     * @param string $userClass
     * @param UserManipulator|callable $manipulator Can either be a UserManipulator or a callable that generates one.
     * @return self
     */
    public function registerManipulator($userClass, $manipulator)
    {
        if (!$manipulator instanceof UserManipulator && !is_callable($manipulator)) {
            throw new \InvalidArgumentException('Manipulator parameter should be a UserManipulator object, or a callable that generates a UserManipulator object.');
        }
        $this->manipulators[$userClass] = $manipulator;

        return $this;
    }

    /**
     * Registers a fallback password manipulator for use if a user is passed with no explicitly associated manipulator.
     *
     * @param PasswordManipulatorInterface $fallback
     * @return self
     */
    public function registerFallback(PasswordManipulatorInterface $fallback)
    {
        $this->fallback = $fallback;

        return $this;
    }
}
