<?php declare(strict_types=1);

namespace Azimo\Apple\Tests\Unit\Auth\Jwt;

use Azimo\Apple\Auth\Exception\InvalidJwtException;
use Azimo\Apple\Auth\Jwt\JwtParser;
use InvalidArgumentException;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Token;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use RuntimeException;

final class JwtParserTest extends MockeryTestCase
{
    /**
     * @var Parser|Mockery\MockInterface
     */
    private $jwtParserMock;

    /**
     * @var JwtParser
     */
    private $parser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->jwtParserMock = Mockery::mock(Parser::class);

        $this->parser = new JwtParser($this->jwtParserMock);
    }

    public function testIfParseReturnsExpectedTokenObjectWhenJwtIsCorrect(): void
    {
        $token = Mockery::mock(Token::class);

        $this->jwtParserMock->shouldReceive('parse')
            ->once()
            ->with('json.web.token')
            ->andReturn($token);

        self::assertSame($token, $this->parser->parse('json.web.token'));
    }

    public function testIfParseThrowsInvalidJwtExceptionWhenTokenParsingFails(): void
    {

        $this->jwtParserMock->shouldReceive('parse')
            ->once()
            ->with('json.web.token')
            ->andThrow(new InvalidArgumentException('The JWT string must have two dots'));

        $this->expectException(InvalidJwtException::class);
        $this->expectExceptionMessage('The JWT string must have two dots');
        $this->parser->parse('json.web.token');
    }

    public function testIfParseThrowsInvalidJwtExceptionWhenContainsIncorrectJsonInPayload(): void
    {

        $this->jwtParserMock->shouldReceive('parse')
            ->once()
            ->with('json.web.token')
            ->andThrow(new RuntimeException('Error while decoding to JSON: '));

        $this->expectException(InvalidJwtException::class);
        $this->expectExceptionMessage('Error while decoding to JSON: ');
        $this->parser->parse('json.web.token');
    }
}
