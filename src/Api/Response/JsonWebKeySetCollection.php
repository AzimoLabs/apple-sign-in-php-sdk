<?php declare(strict_types=1);

namespace Azimo\Apple\Api\Response;

use Azimo\Apple\Api\Exception\UnsupportedCryptographicAlgorithmException;

class JsonWebKeySetCollection
{
    /**
     * @var JsonWebKeySet[]
     */
    private $authKeys;

    public function __construct(array $authKeys)
    {
        $this->authKeys = $authKeys;
    }

    public function getAuthKeys(): array
    {
        return $this->authKeys;
    }

    public function getByCryptographicAlgorithm(string $algorithm): ?JsonWebKeySet
    {
        $result = $this->authKeys[$algorithm] ?? null;
        if(!$result) {
            throw new UnsupportedCryptographicAlgorithmException(
                sprintf(
                    'Cryptographic algorithm `%s` is not supported.',
                    $algorithm
                )
            );
        }

        return $result;
    }
}
