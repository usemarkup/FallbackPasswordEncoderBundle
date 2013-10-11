# MarkupFallbackPasswordEncoderBundle

## About

This Symfony2 bundle offers a strategy to use for password encoding for use with the [friendsofsymfony/user-bundle](https://packagist.org/packages/friendsofsymfony/user-bundle) package (FOSUserBundle).  It requires version >=2.3.6 of the Symfony Security component.

The use case would be when there is legacy user data with passwords that are hashed using an algorithm that is easy to break, such as MD5.  You'd like to [use bcrypt](http://codahale.com/how-to-safely-store-a-password/), but this means getting all users to reset their passwords.  This bundle allows you to declare a stack of encoders, so that you can run a primary algorithm and a set of fallback algorithms at the same time.  A user with a password hashed using the legacy algorithm will have the stored hash transparently updated to the new, more secure hash the next time they sign in.

## Disclaimer

The existence of this software should by no means be construed as condoning the strategy itself.  It is far preferable to have all passwords in your system using the same, secure algorithm.  However, you may judge that this strategy is the most pragmatic for your situation - typically when you do not wish to enforce password resetting on your user base.

## Usage

Configuration example:

A service ID is declared as the primary encoder - this is the canonical encoder that passwords should be hashed with. You then define a stack of fallback encoders that are used to check passwords using legacy algorithms. Manipulators also need to be registered if you are not making use of the `fos_user.util.user_manipulator` service provided by FOSUserBundle. (This service will still be used as a fallback for users of a class that does not appear in the keys of this manipulators list.)

```yml
markup_fallback_password_encoder:
    encoders:
        primary:
            id: security.encoder.blowfish
        fallbacks:
            - id: my_legacy_compat.encoder.md5.saltless
    manipulators:
        My\Bundle\CustomerBundle\Entity\Customer: my_customer.util.manipulator
        My\Bundle\AdminUserBundle\Entity\AdminUser: my_admin_user.util.manipulator
```

In your security.yml file, you would then specify the fallback encoder as `markup_fallback_password_encoder`:

```yml
security:
    encoders:
        My\Bundle\CustomerBundle\Entity\Customer: markup_fallback_password_encoder
        My\Bundle\AdminUserBundle\Entity\AdminUser: markup_fallback_password_encoder
```

## License

Released under the MIT License. See LICENSE.
