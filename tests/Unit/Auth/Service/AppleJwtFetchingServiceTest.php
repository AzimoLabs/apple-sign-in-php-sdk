<?php declare(strict_types=1);

namespace Azimo\Apple\Tests\Unit\Auth\Service;

use Azimo\Apple\Auth\Exception;
use Azimo\Apple\Auth\Factory\AppleJwtStructFactory;
use Azimo\Apple\Auth\Jwt;
use Azimo\Apple\Auth\Service\AppleJwtFetchingService;
use Azimo\Apple\Auth\Struct\JwtPayload;
use DateTimeImmutable;
use Lcobucci\JWT\Token;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class AppleJwtFetchingServiceTest extends MockeryTestCase
{
    /**
     * @var Jwt\JwtParser|Mockery\MockInterface
     */
    private $parserMock;

    /**
     * @var Jwt\JwtVerifier|Mockery\MockInterface
     */
    private $verifierMock;

    /**
     * @var Jwt\JwtValidator|Mockery\MockInterface
     */
    private $validatorMock;

    /**
     * @var AppleJwtStructFactory|Mockery\MockInterface
     */
    private $factoryMock;

    /**
     * @var AppleJwtFetchingService
     */
    private $jwtService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parserMock = Mockery::mock(Jwt\JwtParser::class);
        $this->verifierMock = Mockery::mock(Jwt\JwtVerifier::class);
        $this->validatorMock = Mockery::mock(Jwt\JwtValidator::class);
        $this->factoryMock = Mockery::mock(AppleJwtStructFactory::class);

        $this->jwtService = new AppleJwtFetchingService(
            $this->parserMock,
            $this->verifierMock,
            $this->validatorMock,
            $this->factoryMock
        );
    }

    public function testIfGetJwtPayloadThrowsVerificationFailedExceptionWhenVerificationFails(): void
    {
        $token = Mockery::mock(Token::class);
        $this->parserMock->shouldReceive('parse')
            ->once()
            ->with('json.web.token')
            ->andReturn($token);

        $this->verifierMock->shouldReceive('verify')
            ->once()
            ->with($token)
            ->andReturn(false);

        $this->expectException(Exception\VerificationFailedException::class);
        $this->expectExceptionMessage(
            'Verification of given `json.web.token` token failed. '
            . 'Possibly incorrect public key used or token is malformed.'
        );
        $this->jwtService->getJwtPayload('json.web.token');
    }

    public function testIfGetJwtPayloadThrowsValidationFailedExceptionWhenTokenIsInvalid(): void
    {
        $token = Mockery::mock(Token::class);
        $this->parserMock->shouldReceive('parse')
            ->once()
            ->with('json.web.token')
            ->andReturn($token);

        $this->verifierMock->shouldReceive('verify')
            ->once()
            ->with($token)
            ->andReturn(true);

        $this->validatorMock->shouldReceive('isValid')
            ->once()
            ->with($token)
            ->andReturn(false);

        $this->expectException(Exception\ValidationFailedException::class);
        $this->expectExceptionMessage('Validation of given token failed. Possibly token expired.');
        $this->jwtService->getJwtPayload('json.web.token');
    }

    public function testIfGetJwtPayloadReturnsExpectedJwtPayloadWhenTokenIsVerifiedAndValid(): void
    {
        $currentDate = new DateTimeImmutable();
        $token = Mockery::mock(Token::class);
        $this->parserMock->shouldReceive('parse')
            ->once()
            ->with('json.web.token')
            ->andReturn($token);

        $this->verifierMock->shouldReceive('verify')
            ->once()
            ->with($token)
            ->andReturn(true);

        $this->validatorMock->shouldReceive('isValid')
            ->once()
            ->with($token)
            ->andReturn(true);

        $jwtPayload = new JwtPayload(
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
            true
        );

        $this->factoryMock->shouldReceive('createJwtPayloadFromToken')
            ->once()
            ->with($token)
            ->andReturn($jwtPayload);

        self::assertSame($jwtPayload, $this->jwtService->getJwtPayload('json.web.token'));
    }
}
