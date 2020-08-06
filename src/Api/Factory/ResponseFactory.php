<?php declare(strict_types=1);

namespace Azimo\Apple\Api\Factory;

use Azimo\Apple\Api\Enum\CryptographicAlgorithmEnum;
use Azimo\Apple\Api\Exception\ResponseValidationException;
use Azimo\Apple\Api\Exception\UnsupportedCryptographicAlgorithmException;
use Azimo\Apple\Api\Response\JsonWebKeySet;
use Azimo\Apple\Api\Response\JsonWebKeySetCollection;

class ResponseFactory
{
    public function createFromArray(array $responseBody): JsonWebKeySetCollection
    {
        $this->validateResponse($responseBody);

        $authKeys = [];
        foreach ($responseBody['keys'] as $authKey) {
            $this->validateAuthKey($authKey);

            $authKeys[$authKey['kid']] = new JsonWebKeySet(
                $authKey['kty'],
                $authKey['kid'],
                $authKey['use'],
                $authKey['alg'],
                $authKey['n'],
                $authKey['e']
            );
        }

        return new JsonWebKeySetCollection($authKeys);
    }

    private function validateResponse(array $responseBody): void
    {
        if (!isset($responseBody['keys'])) {
            throw new ResponseValidationException('Response is missing `keys` field');
        }

        if (count($responseBody['keys']) < 1) {
            throw new ResponseValidationException('Response is missing auth keys');
        }
    }

    private function validateAuthKey(array $authKey): void
    {
        if (!isset($authKey['kty'], $authKey['kid'], $authKey['use'], $authKey['alg'], $authKey['n'], $authKey['e'])) {
            throw new ResponseValidationException(
                sprintf(
                    'One or more of required fields `kty`,`kid`,`use`,`alg`,`n`,`e` are missing in auth key `%s`',
                    implode(',', array_keys($authKey))
                )
            );
        }

        if (!CryptographicAlgorithmEnum::isSupported($authKey['kid'])) {
            throw new UnsupportedCryptographicAlgorithmException(
                sprintf(
                    'Cryptographic algorithm `%s` is not supported. Supported algorithms: `%s`',
                    $authKey['kid'],
                    implode(',', CryptographicAlgorithmEnum::supportedAlgorithms())
                )
            );
        }
    }
}
