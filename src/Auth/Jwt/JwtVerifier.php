<?php declare(strict_types=1);

namespace Azimo\Apple\Auth\Jwt;

use Azimo\Apple\Api\AppleApiClientInterface;
use Azimo\Apple\Api\Exception as ApiException;
use Azimo\Apple\Api\Response\JsonWebKeySet;
use Azimo\Apple\Auth\Exception;
use Lcobucci\JWT;
use OutOfBoundsException;
use phpseclib3\Crypt\RSA;
use phpseclib3\Math\BigInteger;

class JwtVerifier
{
    private AppleApiClientInterface $client;

    private JWT\Signer $signer;

    private JWT\Validator $validator;

    public function __construct(AppleApiClientInterface $client, JWT\Validator $validator, JWT\Signer $signer)
    {
        $this->client = $client;
        $this->signer = $signer;
        $this->validator = $validator;
    }

    /**
     * @throws Exception\InvalidCryptographicAlgorithmException
     * @throws Exception\KeysFetchingFailedException
     */
    public function verify(JWT\Token $jwt): bool
    {
        return $this->validator->validate(
            $jwt,
            new JWT\Validation\Constraint\SignedWith(
                $this->signer,
                JWT\Signer\Key\InMemory::plainText($this->createPublicKey($this->getAuthKey($jwt)))
            )
        );
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
            $cryptographicAlgorithm = $jwt->headers()->get('kid');
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
