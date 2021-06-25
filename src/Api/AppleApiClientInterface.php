<?php declare(strict_types=1);

namespace Azimo\Apple\Api;

use Azimo\Apple\Api\Exception as ApiException;

interface AppleApiClientInterface
{
    /**
     * @throws ApiException\AppleApiExceptionInterface
     */
    public function getAuthKeys(): Response\JsonWebKeySetCollection;
}
