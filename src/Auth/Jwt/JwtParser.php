<?php declare(strict_types=1);

namespace Azimo\Apple\Auth\Jwt;

use Azimo\Apple\Auth\Exception;
use InvalidArgumentException;
use Lcobucci\JWT;
use RuntimeException;

class JwtParser
{
    /**
     * @var JWT\Parser
     */
    private $jwtParser;

    public function __construct(JWT\Parser $jwtParser)
    {
        $this->jwtParser = $jwtParser;
    }

    /**
     * @throws Exception\InvalidJwtException
     */
    public function parse(string $jwt): JWT\Token
    {
        try {
            return $this->jwtParser->parse($jwt);
        } catch (InvalidArgumentException | RuntimeException $exception) {
            throw new Exception\InvalidJwtException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
