<?php declare(strict_types=1);

namespace Azimo\Apple\Auth\Service;

use Azimo\Apple\Auth\Exception;
use Azimo\Apple\Auth\Factory\AppleJwtStructFactory;
use Azimo\Apple\Auth\Jwt;
use Azimo\Apple\Auth\Struct\JwtPayload;
use Azimo\Apple\Auth\Struct\JwtRefreshIdTokenPayload;

class AppleJwtFetchingService
{
    /**
     * @var Jwt\JwtParser
     */
    private $parser;

    /**
     * @var Jwt\JwtVerifier
     */
    private $verifier;

    /**
     * @var Jwt\JwtValidator
     */
    private $validator;

    /**
     * @var AppleJwtStructFactory
     */
    private $factory;

    public function __construct(
        Jwt\JwtParser $parser,
        Jwt\JwtVerifier $verifier,
        Jwt\JwtValidator $validator,
        AppleJwtStructFactory $factory
    ) {
        $this->parser = $parser;
        $this->verifier = $verifier;
        $this->validator = $validator;
        $this->factory = $factory;
    }

    /**
     * @throws Exception\InvalidCryptographicAlgorithmException
     * @throws Exception\InvalidJwtException
     * @throws Exception\KeysFetchingFailedException
     * @throws Exception\MissingClaimException
     * @throws Exception\NotSignedTokenException
     * @throws Exception\ValidationFailedException
     * @throws Exception\VerificationFailedException
     */
    public function getJwtPayload(string $jwt): JwtPayload
    {
        $parsedJwt = $this->parser->parse($jwt);

        if (!$this->verifier->verify($parsedJwt)) {
            throw new Exception\VerificationFailedException(
                sprintf(
                    'Verification of given `%s` token failed. '
                    . 'Possibly incorrect public key used or token is malformed.',
                    $jwt
                )
            );
        }
        if (!$this->validator->isValid($parsedJwt)) {
            throw new Exception\ValidationFailedException('Validation of given token failed. Possibly token expired.');
        }

        return $this->factory->createJwtPayloadFromToken($parsedJwt);
    }

    public function getRefreshJwtPayload(string $jwt): JwtRefreshIdTokenPayload
    {
        $parsedJwt = $this->parser->parse($jwt);

        if (!$this->verifier->verify($parsedJwt)) {
            throw new Exception\VerificationFailedException(
                sprintf(
                    'Verification of given `%s` token failed. '
                    . 'Possibly incorrect public key used or token is malformed.',
                    $jwt
                )
            );
        }
        if (!$this->validator->isValid($parsedJwt)) {
            throw new Exception\ValidationFailedException('Validation of given token failed. Possibly token expired.');
        }

        return $this->factory->createJwtPayloadFromRefreshIdToken($parsedJwt);
    }
}
