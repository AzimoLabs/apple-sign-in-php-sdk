<?php declare(strict_types=1);

namespace Azimo\Apple\Auth\Jwt;

use DateTimeImmutable;
use Lcobucci\JWT;

class JwtValidator
{
    private JWT\Validator $validator;

    private array $constraints;

    public function __construct(JWT\Validator $validator, array $constraints)
    {
        $this->validator = $validator;
        $this->constraints = $constraints;
    }

    public function isValid(JWT\Token $jwt): bool
    {
        return $this->validator->validate($jwt, ...$this->constraints) && !$jwt->isExpired(new DateTimeImmutable());
    }
}
