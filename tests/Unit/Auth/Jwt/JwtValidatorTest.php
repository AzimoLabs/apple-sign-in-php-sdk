<?php declare(strict_types=1);

namespace Azimo\Apple\Tests\Unit\Auth\Jwt;

use Azimo\Apple\Auth\Jwt\JwtValidator;
use Lcobucci\JWT;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class JwtValidatorTest extends MockeryTestCase
{
    /**
     * @var JWT\ValidationData|Mockery\MockInterface
     */
    private $validationDataMock;

    /**
     * @var JWT\Token|Mockery\MockInterface
     */
    private $jwtMock;

    /**
     * @var JwtValidator
     */
    private $jwtValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validationDataMock = Mockery::mock(JWT\ValidationData::class);
        $this->jwtMock = Mockery::mock(JWT\Token::class);

        $this->jwtValidator = new JwtValidator($this->validationDataMock);
    }

    public function testIfIsValidReturnsTrueWhenTokenDataAreValidAndTokenIsNotExpired(): void
    {
        $this->jwtMock->shouldReceive('validate')
            ->once()
            ->with($this->validationDataMock)
            ->andReturn(true);
        $this->jwtMock->shouldReceive('isExpired')
            ->once()
            ->withNoArgs()
            ->andReturn(false);

        self::assertTrue($this->jwtValidator->isValid($this->jwtMock));
    }

    public function testIfIsValidReturnsFalseWhenTokenDataAreValidAndTokenIsExpired(): void
    {
        $this->jwtMock->shouldReceive('validate')
            ->once()
            ->with($this->validationDataMock)
            ->andReturn(true);
        $this->jwtMock->shouldReceive('isExpired')
            ->once()
            ->withNoArgs()
            ->andReturn(true);

        self::assertFalse($this->jwtValidator->isValid($this->jwtMock));
    }

    public function testIfIsValidReturnsFalseWhenTokenDataAreInvalidValidAndTokenIsNotExpired(): void
    {
        $this->jwtMock->shouldReceive('validate')
            ->once()
            ->with($this->validationDataMock)
            ->andReturn(false);
        $this->jwtMock->shouldNotReceive('isExpired');

        self::assertFalse($this->jwtValidator->isValid($this->jwtMock));
    }
}
