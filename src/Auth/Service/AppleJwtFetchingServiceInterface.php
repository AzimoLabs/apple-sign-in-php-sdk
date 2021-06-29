<?php declare(strict_types=1);

namespace Azimo\Apple\Auth\Service;

use Azimo\Apple\Auth\Exception;
use Azimo\Apple\Auth\Struct\JwtPayload;

interface AppleJwtFetchingServiceInterface
{
    /**
     * @throws Exception\InvalidCryptographicAlgorithmException
     * @throws Exception\InvalidJwtException
     * @throws Exception\KeysFetchingFailedException
     * @throws Exception\MissingClaimException
     * @throws Exception\ValidationFailedException
     * @throws Exception\VerificationFailedException
     */
    public function getJwtPayload(string $jwt): JwtPayload;
}