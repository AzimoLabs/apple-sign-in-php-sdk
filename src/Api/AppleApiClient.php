<?php declare(strict_types=1);

namespace Azimo\Apple\Api;

use Azimo\Apple\Api\Exception\PublicKeyFetchingFailedException;
use Azimo\Apple\Api\Factory\ResponseFactory;
use GuzzleHttp;

class AppleApiClient
{
    /**
     * @var GuzzleHttp\ClientInterface
     */
    private $httpClient;

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    public function __construct(GuzzleHttp\ClientInterface $httpClient, ResponseFactory $responseFactory)
    {
        $this->httpClient = $httpClient;
        $this->responseFactory = $responseFactory;
    }

    public function getAuthKeys(): Response\JsonWebKeySetCollection
    {
        try {
            $response = $this->httpClient->send(new GuzzleHttp\Psr7\Request('GET', 'auth/keys'));
        } catch (GuzzleHttp\Exception\GuzzleException $exception) {
            throw new PublicKeyFetchingFailedException($exception->getMessage(), $exception->getCode(), $exception);
        }

        try {
            return $this->responseFactory->createFromArray(
                GuzzleHttp\json_decode($response->getBody()->getContents(), true)
            );
        } catch (\InvalidArgumentException $exception) {
            throw new Exception\InvalidResponseException(
                'Unable to decode response',
                $exception->getCode(),
                $exception
            );
        }
    }
}
