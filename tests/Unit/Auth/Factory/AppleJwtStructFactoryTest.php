<?php declare(strict_types=1);

namespace Azimo\Apple\Tests\Unit\Auth\Factory;

use Azimo\Apple\Auth\Exception\MissingClaimException;
use Azimo\Apple\Auth\Factory\AppleJwtStructFactory;
use Azimo\Apple\Auth\Struct\JwtPayload;
use Lcobucci\JWT\Claim\EqualsTo;
use Lcobucci\JWT\Token;

class AppleJwtStructFactoryTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    /**
     * @var AppleJwtStructFactory
     */
    private $appleJwtStructFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->appleJwtStructFactory = new AppleJwtStructFactory();
    }

    public function testIfCreateJwtPayloadFromTokenReturnsExpectedJsonPayload(): void
    {
        $this->assertEquals(
            new JwtPayload(
                'https://appleid.apple.com',
                'com.acme.app',
                1591622611,
                1591622011,
                'foo.bar.baz',
                'qGzMhtsfTCom-bl1PJYLHk',
                'foo@privaterelay.appleid.com',
                true,
                true,
                1591622011,
                true
            ),
            $this->appleJwtStructFactory->createJwtPayloadFromToken(
                new Token(
                    [
                        'kid' => 'eXaunmL',
                        'alg' => 'RS256',
                    ],
                    [
                        'iss'              => new EqualsTo('iss', 'https://appleid.apple.com'),
                        'aud'              => new EqualsTo('aud', 'com.acme.app'),
                        'exp'              => new EqualsTo('exp', 1591622611),
                        'iat'              => new EqualsTo('iat', 1591622011),
                        'sub'              => new EqualsTo('sub', 'foo.bar.baz'),
                        'c_hash'           => new EqualsTo('c_hash', 'qGzMhtsfTCom-bl1PJYLHk'),
                        'email'            => new EqualsTo('email', 'foo@privaterelay.appleid.com'),
                        'email_verified'   => new EqualsTo('email_verified', 'true'),
                        'is_private_email' => new EqualsTo('is_private_email', 'true'),
                        'auth_time'        => new EqualsTo('auth_time', 1591622011),
                        'nonce_supported'  => new EqualsTo('nonce_supported', true),
                    ]
                )
            )
        );
    }

    public function testIfCreateJwtPayloadFromTokenThrowsMissingClaimExceptionWhenAnyClaimIsMissing(): void
    {
        $this->expectExceptionMessage('Requested claim is not configured');
        $this->expectException(MissingClaimException::class);
        $this->appleJwtStructFactory->createJwtPayloadFromToken(
            new Token(
                [
                    'kid' => 'eXaunmL',
                    'alg' => 'RS256',
                ],
                [
                    'iss'              => new EqualsTo('iss', 'https://appleid.apple.com'),
                    'aud'              => new EqualsTo('aud', 'com.acme.app'),
                    'exp'              => new EqualsTo('exp', 1591622611),
                    'iat'              => new EqualsTo('iat', 1591622011),
                    'sub'              => new EqualsTo('sub', 'foo.bar.baz'),
                    'email'            => new EqualsTo('email', 'foo@privaterelay.appleid.com'),
                    'email_verified'   => new EqualsTo('email_verified', 'true'),
                    'is_private_email' => new EqualsTo('is_private_email', 'true'),
                    'auth_time'        => new EqualsTo('auth_time', 1591622011),
                    'nonce_supported'  => new EqualsTo('nonce_supported', true),
                ]
            )
        );
    }
}
