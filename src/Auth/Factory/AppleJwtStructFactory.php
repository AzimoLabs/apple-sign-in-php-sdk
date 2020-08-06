<?php declare(strict_types=1);

namespace Azimo\Apple\Auth\Factory;

use Azimo\Apple\Auth\Exception\MissingClaimException;
use Azimo\Apple\Auth\Struct\JwtPayload;
use Lcobucci\JWT\Token;
use OutOfBoundsException;

class AppleJwtStructFactory
{
    /**
     * @throws MissingClaimException
     */
    public function createJwtPayloadFromToken(Token $token): JwtPayload
    {
        try {
            return new JwtPayload(
                $token->getClaim('iss'),
                $token->getClaim('aud'),
                $token->getClaim('exp'),
                $token->getClaim('iat'),
                $token->getClaim('sub'),
                $token->getClaim('c_hash'),
                $token->getClaim('email', ''),
                // For some reason Apple API returns boolean flag as a string
                (string) $token->getClaim('email_verified', 'false') === 'true',
                // For some reason Apple API returns boolean flag as a string
                (string) $token->getClaim('is_private_email', 'false') === 'true',
                $token->getClaim('auth_time'),
                $token->getClaim('nonce_supported', false)
            );
        } catch (OutOfBoundsException $exception) {
            throw new MissingClaimException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
