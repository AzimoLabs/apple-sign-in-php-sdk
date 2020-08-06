<?php declare(strict_types=1);

namespace Azimo\Apple\Auth\Jwt;

use Azimo\Apple\Api\AppleApiClient;
use Azimo\Apple\Api\Exception as ApiException;
use Azimo\Apple\Api\Response\JsonWebKeySet;
use Azimo\Apple\Auth\Exception;
use BadMethodCallException;
use Lcobucci\JWT;
use OutOfBoundsException;
use phpseclib\Crypt\RSA;
use phpseclib\Math\BigInteger;

class JwtVerifier
{
    /**
     * @var AppleApiClient
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

    public function __construct(AppleApiClient $client, RSA $rsa, JWT\Signer $signer)
    {
        $this->client = $client;
        $this->rsa = $rsa;
        $this->signer = $signer;
    }

    /**
     * @throws Exception\InvalidCryptographicAlgorithmException
     * @throws Exception\KeysFetchingFailedException
     * @throws Exception\NotSignedTokenException
     */
    public function verify(JWT\Token $jwt): bool
    {
        $this->loadRsaKey($this->getAuthKey($jwt));

        try {
            return $jwt->verify($this->signer, $this->rsa->getPublicKey());
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
