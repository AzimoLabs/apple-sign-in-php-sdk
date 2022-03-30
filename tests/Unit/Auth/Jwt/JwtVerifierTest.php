<?php declare(strict_types=1);

namespace Tests\Unit\Azimo\Apple\Auth\Jwt;

use Azimo\Apple\APi;
use Azimo\Apple\Auth\Exception;
use Azimo\Apple\Auth\Jwt\JwtVerifier;
use BadMethodCallException;
use Lcobucci\JWT;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OutOfBoundsException;
use phpseclib\Crypt\RSA;
use phpseclib\Math\BigInteger;

class JwtVerifierTest extends MockeryTestCase
{
    /**
     * @var APi\AppleApiClient|Mockery\MockInterface
     */
    private $appleApiClient;

    /**
     * @var RSA|Mockery\MockInterface
     */
    private $rsaMock;

    /**
     * @var JWT\Signer\Rsa\Sha256|Mockery\MockInterface
     */
    private $signerMock;

    /**
     * @var JWT\Token|Mockery\MockInterface
     */
    private $jwtMock;

    /**
     * @var JwtVerifier
     */
    private $jwtVerifier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->appleApiClient = Mockery::mock(Api\AppleApiClient::class);
        $this->rsaMock = Mockery::mock(RSA::class);
        $this->signerMock = Mockery::mock(JWT\Signer\Rsa\Sha256::class);
        $this->jwtMock = Mockery::mock(JWT\Token::class);

        $this->jwtVerifier = new JwtVerifier($this->appleApiClient, $this->rsaMock, $this->signerMock);
    }

    public function testIfVerifyThrowsKeysFetchingFailedExceptionWhenFailedToFetchJsonWebKeySet(): void
    {
        $this->appleApiClient->shouldReceive('getAuthKeys')
            ->once()
            ->withNoArgs()
            ->andThrow(new Api\Exception\PublicKeyFetchingFailedException('Connection error'));

        $this->expectException(Exception\KeysFetchingFailedException::class);
        $this->expectExceptionMessage('Connection error');
        $this->jwtVerifier->verify($this->jwtMock);
    }

    public function testIfVerifyThrowsInvalidCryptographicAlgorithmExceptionWhenKidHeaderNotExist(): void
    {
        $this->appleApiClient->shouldReceive('getAuthKeys')
            ->once()
            ->withNoArgs()
            ->andReturn(new Api\Response\JsonWebKeySetCollection([]));

        $this->jwtMock->shouldReceive('getHeader')
            ->once()
            ->with('kid')
            ->andThrow(new OutOfBoundsException('`kid` header is missing'));

        $this->expectException(Exception\InvalidCryptographicAlgorithmException::class);
        $this->expectExceptionMessage('`kid` header is missing');
        $this->jwtVerifier->verify($this->jwtMock);
    }

    public function testIfVerifyThrowsInvalidCryptographicAlgorithmExceptionWhenAlgorithmIsNotSupported(): void
    {
        $this->appleApiClient->shouldReceive('getAuthKeys')
            ->once()
            ->withNoArgs()
            ->andReturn(new Api\Response\JsonWebKeySetCollection([]));

        $this->jwtMock->shouldReceive('getHeader')
            ->once()
            ->with('kid')
            ->andReturn('foo');

        $this->expectException(Exception\InvalidCryptographicAlgorithmException::class);
        $this->expectExceptionMessage(
            'Cryptographic algorithm `foo` is not supported.'
        );
        $this->jwtVerifier->verify($this->jwtMock);
    }

    public function testIfVerifyThrowsInvalidCryptographicAlgorithmExceptionWhenAuthKeyNotExistForGivenAlgorithm(): void
    {
        $this->appleApiClient->shouldReceive('getAuthKeys')
            ->once()
            ->withNoArgs()
            ->andReturn(new Api\Response\JsonWebKeySetCollection([]));

        $this->jwtMock->shouldReceive('getHeader')
            ->once()
            ->with('kid')
            ->andReturn('86D88Kf');

        $this->expectException(Exception\InvalidCryptographicAlgorithmException::class);
        $this->expectExceptionMessage('Cryptographic algorithm `86D88Kf` is not supported.');
        $this->jwtVerifier->verify($this->jwtMock);
    }

    public function testIfVerifyThrowsNotSignedTokenExceptionWhenTokenIsMissingSignature(): void
    {
        $this->appleApiClient->shouldReceive('getAuthKeys')
            ->once()
            ->withNoArgs()
            ->andReturn(
                new Api\Response\JsonWebKeySetCollection(
                    [
                        '86D88Kf' => new Api\Response\JsonWebKeySet(
                            'RSA',
                            '86D88Kf',
                            'sig',
                            'RS256',
                            'iGaLqP6y-SJCCBq5Hv6pGDbG_SQ11MNjH7rWHcCFYz4hGwHC4lcSurTlV8u3avoVNM8jXevG1Iu1SY11qInqUvjJur--hghr1b56OPJu6H1iKulSxGjEIyDP6c5BdE1uwprYyr4IO9th8fOwCPygjLFrh44XEGbDIFeImwvBAGOhmMB2AD1n1KviyNsH0bEB7phQtiLk-ILjv1bORSRl8AK677-1T8isGfHKXGZ_ZGtStDe7Lu0Ihp8zoUt59kx2o9uWpROkzF56ypresiIl4WprClRCjz8x6cPZXU2qNWhu71TQvUFwvIvbkE1oYaJMb0jcOTmBRZA2QuYw-zHLwQ',
                            'AQAB'
                        ),
                    ]
                )
            );

        $this->jwtMock->shouldReceive('getHeader')
            ->once()
            ->with('kid')
            ->andReturn('86D88Kf');

        $this->rsaMock->shouldReceive('loadKey')
            ->once()
            ->with(
                Mockery::on(
                    function (array $argument) {
                        $this->assertArrayHasKey('exponent', $argument);
                        $this->assertArrayHasKey('modulus', $argument);
                        $this->assertInstanceOf(BigInteger::class, $argument['exponent']);
                        $this->assertInstanceOf(BigInteger::class, $argument['modulus']);

                        return true;
                    }
                )
            );
        $this->rsaMock->shouldReceive('getPublicKey')
            ->withNoArgs()
            ->once()
            ->andReturn('publicKey');

        $this->jwtMock->shouldReceive('verify')
            ->once()
            ->with($this->signerMock, 'publicKey')
            ->andThrow(new BadMethodCallException('This token is not signed'));

        $this->expectException(Exception\NotSignedTokenException::class);
        $this->expectExceptionMessage('This token is not signed');
        $this->jwtVerifier->verify($this->jwtMock);
    }

    public function testIfVerifyReturnsTrueWhenTokenIsCorrect(): void
    {
        $this->appleApiClient->shouldReceive('getAuthKeys')
            ->once()
            ->withNoArgs()
            ->andReturn(
                new Api\Response\JsonWebKeySetCollection(
                    [
                        '86D88Kf' => new Api\Response\JsonWebKeySet(
                            'RSA',
                            '86D88Kf',
                            'sig',
                            'RS256',
                            'iGaLqP6y-SJCCBq5Hv6pGDbG_SQ11MNjH7rWHcCFYz4hGwHC4lcSurTlV8u3avoVNM8jXevG1Iu1SY11qInqUvjJur--hghr1b56OPJu6H1iKulSxGjEIyDP6c5BdE1uwprYyr4IO9th8fOwCPygjLFrh44XEGbDIFeImwvBAGOhmMB2AD1n1KviyNsH0bEB7phQtiLk-ILjv1bORSRl8AK677-1T8isGfHKXGZ_ZGtStDe7Lu0Ihp8zoUt59kx2o9uWpROkzF56ypresiIl4WprClRCjz8x6cPZXU2qNWhu71TQvUFwvIvbkE1oYaJMb0jcOTmBRZA2QuYw-zHLwQ',
                            'AQAB'
                        ),
                    ]
                )
            );

        $this->jwtMock->shouldReceive('getHeader')
            ->once()
            ->with('kid')
            ->andReturn('86D88Kf');

        $this->rsaMock->shouldReceive('loadKey')
            ->once()
            ->with(
                Mockery::on(
                    function (array $argument) {
                        $this->assertArrayHasKey('exponent', $argument);
                        $this->assertArrayHasKey('modulus', $argument);
                        $this->assertInstanceOf(BigInteger::class, $argument['exponent']);
                        $this->assertInstanceOf(BigInteger::class, $argument['modulus']);

                        return true;
                    }
                )
            );
        $this->rsaMock->shouldReceive('getPublicKey')
            ->withNoArgs()
            ->once()
            ->andReturn('publicKey');

        $this->jwtMock->shouldReceive('verify')
            ->once()
            ->with($this->signerMock, 'publicKey')
            ->andReturn(true);

        self::assertTrue($this->jwtVerifier->verify($this->jwtMock));
    }

    public function testIfVerifyReturnsTrueWhenTokenIsCorrectMalformed(): void
    {
        $this->appleApiClient->shouldReceive('getAuthKeys')
            ->once()
            ->withNoArgs()
            ->andReturn(
                new Api\Response\JsonWebKeySetCollection(
                    [
                        '86D88Kf' => new Api\Response\JsonWebKeySet(
                            'RSA',
                            '86D88Kf',
                            'sig',
                            'RS256',
                            'iGaLqP6y-SJCCBq5Hv6pGDbG_SQ11MNjH7rWHcCFYz4hGwHC4lcSurTlV8u3avoVNM8jXevG1Iu1SY11qInqUvjJur--hghr1b56OPJu6H1iKulSxGjEIyDP6c5BdE1uwprYyr4IO9th8fOwCPygjLFrh44XEGbDIFeImwvBAGOhmMB2AD1n1KviyNsH0bEB7phQtiLk-ILjv1bORSRl8AK677-1T8isGfHKXGZ_ZGtStDe7Lu0Ihp8zoUt59kx2o9uWpROkzF56ypresiIl4WprClRCjz8x6cPZXU2qNWhu71TQvUFwvIvbkE1oYaJMb0jcOTmBRZA2QuYw-zHLwQ',
                            'AQAB'
                        ),
                    ]
                )
            );

        $this->jwtMock->shouldReceive('getHeader')
            ->once()
            ->with('kid')
            ->andReturn('86D88Kf');

        $this->rsaMock->shouldReceive('loadKey')
            ->once()
            ->with(
                Mockery::on(
                    function (array $argument) {
                        $this->assertArrayHasKey('exponent', $argument);
                        $this->assertArrayHasKey('modulus', $argument);
                        $this->assertInstanceOf(BigInteger::class, $argument['exponent']);
                        $this->assertInstanceOf(BigInteger::class, $argument['modulus']);

                        return true;
                    }
                )
            );
        $this->rsaMock->shouldReceive('getPublicKey')
            ->withNoArgs()
            ->once()
            ->andReturn('publicKey');

        $this->jwtMock->shouldReceive('verify')
            ->once()
            ->with($this->signerMock, 'publicKey')
            ->andReturn(false);

        self::assertFalse($this->jwtVerifier->verify($this->jwtMock));
    }
}
