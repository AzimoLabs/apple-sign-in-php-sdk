# Sign-in with Apple SDK

[![Build Status](https://img.shields.io/github/workflow/status/AzimoLabs/apple-sign-in-php-sdk/CI?label=ci%20build&style=flat-square)](https://github.com/AzimoLabs/apple-sign-in-php-sdk/actions?query=workflow%3ACI)

## Installation

Recommended and easiest way to installing library is through [Composer](https://getcomposer.org/).

`composer require azimolabs/apple-sign-in-php-sdk`

## Requirements

* PHP 7.1+
* OpenSSL Extension

## PHP support
|PHP version|Library version|
|---|---|
|`5.x`|`NOT SUPPORTED`|
| `> 7.0 <= 7.3`| `1.4.x` |
| `> 7.4 < 8.0`| `1.5.x` |
| `> 8.0 & ^7.4`| `2.0.x` |

## How it works

This description assumes that you already have
generated [identityToken](https://developer.apple.com/documentation/authenticationservices/asauthorizationsinglesignoncredential/3153080-identitytoken)
. Remember that token is valid ONLY for 10 minutes.

The first step to verify the identity token is to generate a public key. To generate public key `exponent` and `modulus`
values are required. Both information are exposed in [Apple API endpoint](https://appleid.apple.com/auth/keys). Those
values differ depending on the algorithm.

The second step is verification if provided `identityToken` is valid against generated public key. If so we are sure
that `identityToken` wasn't malformed.

The third step is validation if token is not expired. Additionally it is worth to check `issuer` and `audience`,
examples are shown below.

## Basic usage

Once you have cloned repository, make sure that composer dependencies are installed running `composer install -o`.

```php
$validationData = new ValidationData();
$validationData->setIssuer('https://appleid.apple.com');
$validationData->setAudience('com.azimo');

$appleJwtFetchingService = new Auth\Service\AppleJwtFetchingService(
            new Auth\Jwt\JwtParser(new \Lcobucci\JWT\Token\Parser(new \Lcobucci\JWT\Encoding\JoseEncoder())),
            new Auth\Jwt\JwtVerifier(
                new Api\AppleApiClient(
                    new GuzzleHttp\Client(
                        [
                            'base_uri'        => 'https://appleid.apple.com',
                            'timeout'         => 5,
                            'connect_timeout' => 5,
                        ]
                    ),
                    new Api\Factory\ResponseFactory()
                ),
                new \Lcobucci\JWT\Validation\Validator(),
                new \Lcobucci\JWT\Signer\Rsa\Sha256()
            ),
            new Auth\Jwt\JwtValidator(
                new \Lcobucci\JWT\Validation\Validator(),
                [
                    new \Lcobucci\JWT\Validation\Constraint\IssuedBy('https://appleid.apple.com'),
                    new \Lcobucci\JWT\Validation\Constraint\PermittedFor('com.c.azimo.stage'),
                ]
            ),
            new Auth\Factory\AppleJwtStructFactory()
        );

$appleJwtFetchingService->getJwtPayload('your.identity.token');
```

If you don't want to copy-paste above code you can paste freshly generated `identityToken`
in `tests/E2e/Auth/AppleJwtFetchingServiceTest.php:53`
and run tests with simple command `php vendor/bin/phpunit tests/E2e`.

```shell script
$ php vendor/bin/phpunit tests/E2e
PHPUnit 9.2.5 by Sebastian Bergmann and contributors.

Random seed:   1594414420

.                                                                   1 / 1 (100%)

Time: 00:00.962, Memory: 8.00 MB

OK (1 test, 1 assertion)
```

## Todo

It is welcome to open a pull request with a fix of any issue:

- [x] Upgrade `phpseclib/phpseclib` to version `3.0.7`
- [x] Upgrade `lcobucci/jwt` to version `4.x`. Reported
  in: [Implicit conversion of keys from strings is deprecated. #2](https://github.com/AzimoLabs/apple-sign-in-php-sdk/issues/2)
- [x] Make library compatible with PHP `7.4.3`. Reported
  in [Uncaught JsonException: Malformed UTF-8 characters](https://github.com/AzimoLabs/apple-sign-in-php-sdk/issues/4)
- [x] Make library compatible with PHP `8.0.0`

## Miscellaneous

* [JSON web token](https://jwt.io/)
* [Sign in with Apple overwiew](https://developer.apple.com/documentation/sign_in_with_apple/sign_in_with_apple_rest_api/authenticating_users_with_sign_in_with_apple)
* [How backend token verification works](https://sarunw.com/posts/sign-in-with-apple-3/)

# Towards financial services available to all

We’re working throughout the company to create faster, cheaper, and more available financial services all over the
world, and here are some of the techniques that we’re utilizing. There’s still a long way ahead of us, and if you’d like
to be part of that journey, check out our [careers page](https://bit.ly/3vajnu6).
