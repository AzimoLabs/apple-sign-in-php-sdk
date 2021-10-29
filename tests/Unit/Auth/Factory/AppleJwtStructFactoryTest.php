<?php declare(strict_types=1);

namespace Azimo\Apple\Tests\Unit\Auth\Factory;

use Azimo\Apple\Auth\Factory\AppleJwtStructFactory;
use Azimo\Apple\Auth\Struct\JwtPayload;
use DateTimeImmutable;
use Lcobucci\JWT\Token;
use Mockery\Adapter\Phpunit\MockeryTestCase;

final class AppleJwtStructFactoryTest extends MockeryTestCase
{
    private AppleJwtStructFactory $appleJwtStructFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->appleJwtStructFactory = new AppleJwtStructFactory();
    }

    public function testIfCreateJwtPayloadFromTokenReturnsExpectedJsonPayload(): void
    {
        $currentDate = new DateTimeImmutable();

        self::assertEquals(
            new JwtPayload(
                'https://appleid.apple.com',
                ['com.acme.app'],
                $currentDate,
                $currentDate,
                'foo.bar.baz',
                'qGzMhtsfTCom-bl1PJYLHk',
                'foo@privaterelay.appleid.com',
                true,
                true,
                1591622011,
                true,
                null
            ),
            $this->appleJwtStructFactory->createJwtPayloadFromToken(
                new Token\Plain(
                    new Token\DataSet(
                        [
                            'kid' => 'eXaunmL',
                            'alg' => 'RS256',
                        ], ''
                    ),
                    new Token\DataSet(
                        [
                            'iss'              => 'https://appleid.apple.com',
                            'aud'              => ['com.acme.app'],
                            'exp'              => $currentDate,
                            'iat'              => $currentDate,
                            'sub'              => 'foo.bar.baz',
                            'c_hash'           => 'qGzMhtsfTCom-bl1PJYLHk',
                            'email'            => 'foo@privaterelay.appleid.com',
                            'email_verified'   => 'true',
                            'is_private_email' => 'true',
                            'auth_time'        => 1591622011,
                            'nonce_supported'  => true
                        ], ''
                    ),
                    Token\Signature::fromEmptyData()
                )
            )
        );
    }
}
