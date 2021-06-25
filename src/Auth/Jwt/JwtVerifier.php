<?php declare(strict_types=1);

namespace Azimo\Apple\Auth\Jwt;

use Azimo\Apple\Api\AppleApiClient;
use Azimo\Apple\Api\Exception as ApiException;
use Azimo\Apple\Api\Response\JsonWebKeySet;
use Azimo\Apple\Auth\Exception;
use BadMethodCallException;
use Lcobucci\JWT;
use OutOfBoundsException;
use phpseclib3\Crypt\RSA;
use phpseclib3\Math\BigInteger;

class JwtVerifier
{
    /**
     * @var AppleApiClient
     */
    private $client;

    /**
     * @var JWT\Signer
     */
    private $signer;

    public function __construct(AppleApiClient $client, JWT\Signer $signer)
    {
        $this->client = $client;
        $this->signer = $signer;
    }

    /**
     * @throws Exception\InvalidCryptographicAlgorithmException
     * @throws Exception\KeysFetchingFailedException
     * @throws Exception\NotSignedTokenException
     */
    public function verify(JWT\Token $jwt): bool
    {
        try {
            return $jwt->verify($this->signer, $this->createPublicKey($this->getAuthKey($jwt)));
        } catch (BadMethodCallException $exception) {
            throw  new Exception\NotSignedTokenException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * @throws Exception\InvalidCryptographicAlgorithmException
     * @throws Exception\KeysFetchingFailedException
     */
    private function getAuthKey(JWT\Token $jwt): JsonWebKeySet
    {
        try {
            $authKeys = $this->client->getAuthKeys();
        } catch (ApiException\AppleApiExceptionInterface $exception) {
            throw new Exception\KeysFetchingFailedException(
                $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }

        try {
            $cryptographicAlgorithm = $jwt->getHeader('kid');
            $authKey = $authKeys->getByCryptographicAlgorithm($cryptographicAlgorithm);
        } catch (OutOfBoundsException | ApiException\UnsupportedCryptographicAlgorithmException $exception) {
            throw new Exception\InvalidCryptographicAlgorithmException(
                $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }

        if (!$authKey) {
            throw new Exception\InvalidCryptographicAlgorithmException(
                sprintf('Unsupported cryptographic algorithm passed `%s', $cryptographicAlgorithm)
            );
        }

        return $authKey;
    }

    private function createPublicKey(JsonWebKeySet $authKey): string
    {
        return RSA\Formats\Keys\PKCS8::savePublicKey(
            new BigInteger(base64_decode(strtr($authKey->getModulus(), '-_', '+/')), 256),
            new BigInteger(base64_decode(strtr($authKey->getExponent(), '-_', '+/')), 256)
        );
    }
}
