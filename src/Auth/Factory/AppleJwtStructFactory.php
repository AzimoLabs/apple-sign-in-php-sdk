<?php declare(strict_types=1);

namespace Azimo\Apple\Auth\Factory;

use Azimo\Apple\Auth\Exception\MissingClaimException;
use Azimo\Apple\Auth\Struct\JwtPayload;
use Lcobucci\JWT\Token;

class AppleJwtStructFactory
{
    /**
     * @throws MissingClaimException
     */
    public function createJwtPayloadFromToken(Token $token): JwtPayload
    {
        $claims = $token->claims();

        return new JwtPayload(
            $claims->get('iss'),
            $claims->get('aud'),
            $claims->get('exp'),
            $claims->get('iat'),
            $claims->get('sub'),
            $claims->get('c_hash', ''),
            $claims->get('email', ''),
            // For some reason Apple API returns boolean flag as a string
            (string) $claims->get('email_verified', 'false') === 'true',
            // For some reason Apple API returns boolean flag as a string
            (string) $claims->get('is_private_email', 'false') === 'true',
            $claims->get('auth_time'),
            $claims->get('nonce_supported', false)
        );
    }
}
