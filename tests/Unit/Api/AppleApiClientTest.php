<?php declare(strict_types=1);

namespace Azimo\Apple\Tests\Unit\Api;

use Azimo\Apple\Api;
use Azimo\Apple\Api\Exception\PublicKeyFetchingFailedException;
use GuzzleHttp;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Http\Message\RequestInterface;

final class AppleApiClientTest extends MockeryTestCase
{
    private Api\AppleApiClient $client;

    private GuzzleHttp\Client $httpClientMock;

    private Api\Factory\ResponseFactory $responseFactoryMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClientMock = Mockery::mock(GuzzleHttp\Client::class);
        $this->responseFactoryMock = Mockery::mock(Api\Factory\ResponseFactory::class);

        $this->client = new Api\AppleApiClient($this->httpClientMock, $this->responseFactoryMock);
    }

    public function testIfGetAuthKeyThrowsPublicKeyFetchingFailedExceptionWhenCouldNotConnectToAppleApi(): void
    {
        $request = new GuzzleHttp\Psr7\Request('GET', 'auth/keys');
        $this->httpClientMock->shouldReceive('send')
            ->once()
            ->with(
                Mockery::on(
                    function (RequestInterface $request) {
                        $this->assertSame('GET', $request->getMethod());
                        $this->assertSame('auth/keys', $request->getUri()->getPath());

                        return true;
                    }
                )
            )
            ->andThrow(
                new GuzzleHttp\Exception\ConnectException(
                    'cURL error 6: Could not resolve host: appleid.apple.com ',
                    $request
                )
            );

        $this->expectException(PublicKeyFetchingFailedException::class);
        $this->expectExceptionMessage('cURL error 6: Could not resolve host: appleid.apple.com ');
        $this->client->getAuthKeys();
    }

    public function testIfGetAuthKeyThrowsInvalidResponseExceptionWhenTryingToDecodeInvalidJsonBody(): void
    {
        $this->httpClientMock->shouldReceive('send')
            ->once()
            ->with(
                Mockery::on(
                    function (RequestInterface $request) {
                        $this->assertSame('GET', $request->getMethod());
                        $this->assertSame('auth/keys', $request->getUri()->getPath());

                        return true;
                    }
                )
            )
            ->andReturn(new GuzzleHttp\Psr7\Response(200, [], 'invalid JSON'));

        $this->expectException(Api\Exception\InvalidResponseException::class);
        $this->expectExceptionMessage('Unable to decode response');
        $this->client->getAuthKeys();
    }

    public function testIfGetAuthKeyReturnsExpectedJsonWebKeySetCollectionOnSuccess(): void
    {
        $responseBody = file_get_contents(__DIR__ . '/data/authKeys.json');

        $this->httpClientMock->shouldReceive('send')
            ->once()
            ->with(
                Mockery::on(
                    function (RequestInterface $request) {
                        $this->assertSame('GET', $request->getMethod());
                        $this->assertSame('auth/keys', $request->getUri()->getPath());

                        return true;
                    }
                )
            )
            ->andReturn(new GuzzleHttp\Psr7\Response(200, [], $responseBody));

        $jsonWebKeySetCollection = new Api\Response\JsonWebKeySetCollection(
            [
                '86D88Kf' => new Api\Response\JsonWebKeySet(
                    'RSA',
                    '86D88Kf',
                    'sig',
                    'RS256',
                    'iGaLqP6y-SJCCBq5Hv6pGDbG_SQ11MNjH7rWHcCFYz4hGwHC4lcSurTlV8u3avoVNM8jXevG1Iu1SY11qInqUvjJur--hghr1b56OPJu6H1iKulSxGjEIyDP6c5BdE1uwprYyr4IO9th8fOwCPygjLFrh44XEGbDIFeImwvBAGOhmMB2AD1n1KviyNsH0bEB7phQtiLk-ILjv1bORSRl8AK677-1T8isGfHKXGZ_ZGtStDe7Lu0Ihp8zoUt59kx2o9uWpROkzF56ypresiIl4WprClRCjz8x6cPZXU2qNWhu71TQvUFwvIvbkE1oYaJMb0jcOTmBRZA2QuYw-zHLwQ',
                    'AQAB'
                ),
                'eXaunmL' => new Api\Response\JsonWebKeySet(
                    'RSA',
                    'eXaunmL',
                    'sig',
                    'RS256',
                    '4dGQ7bQK8LgILOdLsYzfZjkEAoQeVC_aqyc8GC6RX7dq_KvRAQAWPvkam8VQv4GK5T4ogklEKEvj5ISBamdDNq1n52TpxQwI2EqxSk7I9fKPKhRt4F8-2yETlYvye-2s6NeWJim0KBtOVrk0gWvEDgd6WOqJl_yt5WBISvILNyVg1qAAM8JeX6dRPosahRVDjA52G2X-Tip84wqwyRpUlq2ybzcLh3zyhCitBOebiRWDQfG26EH9lTlJhll-p_Dg8vAXxJLIJ4SNLcqgFeZe4OfHLgdzMvxXZJnPp_VgmkcpUdRotazKZumj6dBPcXI_XID4Z4Z3OM1KrZPJNdUhxw',
                    'AQAB'
                ),
            ]
        );

        $this->responseFactoryMock->shouldReceive('createFromArray')
            ->once()
            ->with(
                [
                    'keys' => [
                        [
                            'kty' => 'RSA',
                            'kid' => '86D88Kf',
                            'use' => 'sig',
                            'alg' => 'RS256',
                            'n'   => 'iGaLqP6y-SJCCBq5Hv6pGDbG_SQ11MNjH7rWHcCFYz4hGwHC4lcSurTlV8u3avoVNM8jXevG1Iu1SY11qInqUvjJur--hghr1b56OPJu6H1iKulSxGjEIyDP6c5BdE1uwprYyr4IO9th8fOwCPygjLFrh44XEGbDIFeImwvBAGOhmMB2AD1n1KviyNsH0bEB7phQtiLk-ILjv1bORSRl8AK677-1T8isGfHKXGZ_ZGtStDe7Lu0Ihp8zoUt59kx2o9uWpROkzF56ypresiIl4WprClRCjz8x6cPZXU2qNWhu71TQvUFwvIvbkE1oYaJMb0jcOTmBRZA2QuYw-zHLwQ',
                            'e'   => 'AQAB',
                        ],
                        [
                            'kty' => 'RSA',
                            'kid' => 'eXaunmL',
                            'use' => 'sig',
                            'alg' => 'RS256',
                            'n'   => '4dGQ7bQK8LgILOdLsYzfZjkEAoQeVC_aqyc8GC6RX7dq_KvRAQAWPvkam8VQv4GK5T4ogklEKEvj5ISBamdDNq1n52TpxQwI2EqxSk7I9fKPKhRt4F8-2yETlYvye-2s6NeWJim0KBtOVrk0gWvEDgd6WOqJl_yt5WBISvILNyVg1qAAM8JeX6dRPosahRVDjA52G2X-Tip84wqwyRpUlq2ybzcLh3zyhCitBOebiRWDQfG26EH9lTlJhll-p_Dg8vAXxJLIJ4SNLcqgFeZe4OfHLgdzMvxXZJnPp_VgmkcpUdRotazKZumj6dBPcXI_XID4Z4Z3OM1KrZPJNdUhxw',
                            'e'   => 'AQAB',
                        ],
                    ],
                ]
            )
            ->andReturn($jsonWebKeySetCollection);

        self::assertSame($jsonWebKeySetCollection, $this->client->getAuthKeys());
    }
}
