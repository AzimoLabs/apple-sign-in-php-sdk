<?php declare(strict_types=1);

namespace Azimo\Apple\Tests\Unit\Api\Factory;

use Azimo\Apple\Api;
use Azimo\Apple\Api\Exception\ResponseValidationException;
use Azimo\Apple\Api\Factory\ResponseFactory;
use Azimo\Apple\Api\Response\JsonWebKeySetCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;

final class ResponseFactoryTest extends MockeryTestCase
{
    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->responseFactory = new ResponseFactory();
    }

    public function testIfCreateFromArrayThrowsResponseValidationExceptionWhenKeysIndexIsNotSet(): void
    {
        $this->expectException(ResponseValidationException::class);
        $this->expectExceptionMessage('Response is missing `keys` field');
        $this->responseFactory->createFromArray([]);
    }

    public function testIfCreateFromArrayThrowsResponseValidationExceptionWhenKeysArrayIsEmpty(): void
    {
        $this->expectException(ResponseValidationException::class);
        $this->expectExceptionMessage('Response is missing auth keys');
        $this->responseFactory->createFromArray(['keys' => []]);
    }

    /**
     * @dataProvider provideCreateFromArrayThrowsResponseValidationExceptionWhenKeyArrayMissesRequiredFieldData
     *
     * @param array  $authKey
     * @param string $expectedMessage
     */
    public function testIfCreateFromArrayThrowsResponseValidationExceptionWhenKeyArrayMissesRequiredField(
        array $authKey,
        string $expectedMessage
    ): void {
        $this->expectException(ResponseValidationException::class);
        $this->expectExceptionMessage($expectedMessage);
        $this->responseFactory->createFromArray(['keys' => [$authKey]]);
    }

    public function provideCreateFromArrayThrowsResponseValidationExceptionWhenKeyArrayMissesRequiredFieldData(): array
    {
        return [
            'Missing `kty` field' => [
                [
                    'kid' => '86D88Kf',
                    'use' => 'sig',
                    'alg' => 'RS256',
                    'n'   => 'foo',
                    'e'   => 'AQAB',
                ],
                'One or more of required fields `kty`,`kid`,`use`,`alg`,`n`,`e` are missing in auth key `kid,use,alg,n,e`',
            ],
            'Missing `kid` field' => [
                [
                    'kty' => 'RSA',
                    'use' => 'sig',
                    'alg' => 'RS256',
                    'n'   => 'foo',
                    'e'   => 'AQAB',
                ],
                'One or more of required fields `kty`,`kid`,`use`,`alg`,`n`,`e` are missing in auth key `kty,use,alg,n,e`',
            ],
            'Missing `use` field' => [
                [
                    'kty' => 'RSA',
                    'kid' => '86D88Kf',
                    'alg' => 'RS256',
                    'n'   => 'foo',
                    'e'   => 'AQAB',
                ],
                'One or more of required fields `kty`,`kid`,`use`,`alg`,`n`,`e` are missing in auth key `kty,kid,alg,n,e`',
            ],
            'Missing `alg` field' => [
                [
                    'kty' => 'RSA',
                    'kid' => '86D88Kf',
                    'use' => 'sig',
                    'n'   => 'foo',
                    'e'   => 'AQAB',
                ],
                'One or more of required fields `kty`,`kid`,`use`,`alg`,`n`,`e` are missing in auth key `kty,kid,use,n,e`',
            ],
            'Missing `n` field'   => [
                [
                    'kty' => 'RSA',
                    'kid' => '86D88Kf',
                    'use' => 'sig',
                    'alg' => 'RS256',
                    'e'   => 'AQAB',
                ],
                'One or more of required fields `kty`,`kid`,`use`,`alg`,`n`,`e` are missing in auth key `kty,kid,use,alg,e`',
            ],
            'Missing `e` field'   => [
                [
                    'kty' => 'RSA',
                    'kid' => '86D88Kf',
                    'use' => 'sig',
                    'alg' => 'RS256',
                    'n'   => 'foo',
                ],
                'One or more of required fields `kty`,`kid`,`use`,`alg`,`n`,`e` are missing in auth key `kty,kid,use,alg,n`',
            ],
        ];
    }

    public function testIfCreateFromArraySkipsCreatingJsonWebKeySetWhenKidIsNotSupported(): void
    {
        self::assertEquals(
            new JsonWebKeySetCollection([]),
            $this->responseFactory->createFromArray(
                [
                    'keys' => [
                        [
                            'kty' => 'RSA',
                            'kid' => 'bar',
                            'use' => 'sig',
                            'alg' => 'RS256',
                            'n'   => 'foo',
                            'e'   => 'AQAB',
                        ],
                    ],
                ]
            )
        );
    }

    public function testIfCreateFromArrayReturnsExpectedJsonWebKeySetCollection(): void
    {
        $responseBody = [
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
        ];

        self::assertEquals(
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
                    'eXaunmL' => new Api\Response\JsonWebKeySet(
                        'RSA',
                        'eXaunmL',
                        'sig',
                        'RS256',
                        '4dGQ7bQK8LgILOdLsYzfZjkEAoQeVC_aqyc8GC6RX7dq_KvRAQAWPvkam8VQv4GK5T4ogklEKEvj5ISBamdDNq1n52TpxQwI2EqxSk7I9fKPKhRt4F8-2yETlYvye-2s6NeWJim0KBtOVrk0gWvEDgd6WOqJl_yt5WBISvILNyVg1qAAM8JeX6dRPosahRVDjA52G2X-Tip84wqwyRpUlq2ybzcLh3zyhCitBOebiRWDQfG26EH9lTlJhll-p_Dg8vAXxJLIJ4SNLcqgFeZe4OfHLgdzMvxXZJnPp_VgmkcpUdRotazKZumj6dBPcXI_XID4Z4Z3OM1KrZPJNdUhxw',
                        'AQAB'
                    ),
                ]
            ),
            $this->responseFactory->createFromArray($responseBody)
        );
    }
}
