<?php

namespace Markup\FallbackPasswordEncoderBundle\Exception;

/**
* An exception thrown when a password manipulator service is sought for a user class that has no manipulator declared against it.
*/
class ManipulatorNotRegisteredException extends \RuntimeException implements ExceptionInterface
{}
