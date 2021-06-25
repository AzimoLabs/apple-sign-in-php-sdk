<?php declare(strict_types=1);

namespace Azimo\Apple\Auth\Jwt;

use Azimo\Apple\Api\AppleApiClientInterface;
use Azimo\Apple\Api\Exception as ApiException;
use Azimo\Apple\Api\Response\JsonWebKeySet;
use Azimo\Apple\Auth\Exception;
use Lcobucci\JWT;
use OutOfBoundsException;
use phpseclib\Crypt\RSA;
use phpseclib\Math\BigInteger;

class JwtVerifier
{
    /**
     * @var AppleApiClientInterface
     */
    private $client;

    /**
     * @var RSA
     */
    private $rsa;

    /**
     * @var JWT\Signer
     */
    private $signer;

    /**
     * @var JWT\Validator
     */
    private $validator;

    public function __construct(AppleApiClientInterface $client, JWT\Validator $validator, RSA $rsa, JWT\Signer $signer)
    {
        $this->client = $client;
        $this->rsa = $rsa;
        $this->signer = $signer;
        $this->validator = $validator;
    }

    /**
     * @throws Exception\InvalidCryptographicAlgorithmException
     * @throws Exception\KeysFetchingFailedException
     */
    public function verify(JWT\Token $jwt): bool
    {
        $this->loadRsaKey($this->getAuthKey($jwt));

        return $this->validator->validate(
            $jwt,
            new JWT\Validation\Constraint\SignedWith(
                $this->signer,
                JWT\Signer\Key\InMemory::plainText($this->rsa->getPublicKey())
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

    private function loadRsaKey(JsonWebKeySet $authKey): void
    {
        /**
         * Phpspeclib is parsing phpinfo(); output to determine OpenSSL Library and Header versions,
         * basing on that set if MATH_BIGINTEGER_OPENSSL_ENABLED or MATH_BIGINTEGER_OPENSSL_DISABLED const.
         * It crashes tests so it is possible that it might crash production, that is why constants are overwritten.
         *
         * @see vendor/phpseclib/phpseclib/phpseclib/Math/BigInteger.php:273
         */
        if (!defined('MATH_BIGINTEGER_OPENSSL_ENABLED')) {
            define('MATH_BIGINTEGER_OPENSSL_ENABLED', true);
        }

        $this->rsa->loadKey(
            [
                'exponent' => new BigInteger(base64_decode(strtr($authKey->getExponent(), '-_', '+/')), 256),
                'modulus'  => new BigInteger(base64_decode(strtr($authKey->getModulus(), '-_', '+/')), 256),
            ]
        );
    }
}
