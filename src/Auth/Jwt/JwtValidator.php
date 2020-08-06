<?php declare(strict_types=1);

namespace Azimo\Apple\Auth\Jwt;

use Lcobucci\JWT;

class JwtValidator
{
    /**
     * @var JWT\ValidationData
     */
    private $validationData;

    public function __construct(JWT\ValidationData $validationData)
    {
        $this->validationData = $validationData;
    }

    public function isValid(JWT\Token $jwt): bool
    {
        return $jwt->validate($this->validationData) && !$jwt->isExpired();
    }
}
