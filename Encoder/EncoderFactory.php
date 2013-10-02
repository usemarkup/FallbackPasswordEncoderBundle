<?php

namespace Markup\FallbackPasswordEncoderBundle\Encoder;

use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

/**
* An encoder factory that can set users on a user-aware encoder.
*
* NB. This duplicates code within Symfony\Component\Security\Core\Encoder\EncoderFactory
*/
class EncoderFactory implements EncoderFactoryInterface
{
    protected $encoders;

    public function __construct(array $encoders)
    {
        $this->encoders = $encoders;
    }

    /**
     * {@inheritDoc}
     **/
    public function getEncoder($user)
    {
        foreach ($this->encoders as $class => $encoder) {
            if ((is_object($user) && !$user instanceof $class) || (!is_object($user) && !is_subclass_of($user, $class) && $user != $class)) {
                continue;
            }

            if (!$encoder instanceof PasswordEncoderInterface) {
                return $this->encoders[$class] = $this->createEncoder($encoder);
            }

            if ($encoder instanceof UserAwarePasswordEncoderInterface && is_object($user)) {
                $encoder->setUser($user);
            }

            return $encoder;
        }

        throw new \RuntimeException(sprintf('No encoder has been configured for account "%s".', is_object($user) ? get_class($user) : $user));
    }

    /**
     * Creates the actual encoder instance
     *
     * @param array $config
     *
     * @return PasswordEncoderInterface
     */
    protected function createEncoder(array $config)
    {
        if (!isset($config['class'])) {
            throw new \InvalidArgumentException(sprintf('"class" must be set in %s.', json_encode($config)));
        }
        if (!isset($config['arguments'])) {
            throw new \InvalidArgumentException(sprintf('"arguments" must be set in %s.', json_encode($config)));
        }

        $reflection = new \ReflectionClass($config['class']);

        return $reflection->newInstanceArgs($config['arguments']);
    }
}
