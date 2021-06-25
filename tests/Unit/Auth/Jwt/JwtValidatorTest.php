<?php declare(strict_types=1);

namespace Azimo\Apple\Tests\Unit\Auth\Jwt;

use Azimo\Apple\Auth\Jwt\JwtValidator;
use DateTimeImmutable;
use Lcobucci\JWT;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

final class JwtValidatorTest extends MockeryTestCase
{
    /**
     * @var JWT\Token|Mockery\MockInterface
     */
    private $jwtMock;

    /**
     * @var
     */
    private $constraints;

    /**
     * @var JWT\Validator|Mockery\MockInterface
     */
    private $validatorMock;

    /**
     * @var JwtValidator JwtValidator
     */
    private $jwtValidator;

    /**
     * @var JwtValidator JwtValidator
     */

    protected function setUp(): void
    {
        parent::setUp();

        $this->jwtMock = Mockery::mock(JWT\Token::class);
        $this->constraints = [];
        $this->validatorMock = Mockery::mock(JWT\Validator::class);
        $this->jwtValidator = new JwtValidator($this->validatorMock, $this->constraints);
    }

    public function testIfIsValidReturnsTrueWhenTokenDataAreValidAndTokenIsNotExpired(): void
    {
        $this->validatorMock->shouldReceive('validate')
            ->once()
            ->with($this->jwtMock, ...$this->constraints)
            ->andReturn(true);
        $this->jwtMock->shouldReceive('isExpired')
            ->once()
            ->with(DateTimeImmutable::class)
            ->andReturn(false);

        self::assertTrue($this->jwtValidator->isValid($this->jwtMock));
    }

    public function testIfIsValidReturnsFalseWhenTokenDataAreValidAndTokenIsExpired(): void
    {
        $this->validatorMock->shouldReceive('validate')
            ->once()
            ->with($this->jwtMock, ...$this->constraints)
            ->andReturn(true);
        $this->jwtMock->shouldReceive('isExpired')
            ->once()
            ->with(DateTimeImmutable::class)
            ->andReturn(true);

        self::assertFalse($this->jwtValidator->isValid($this->jwtMock));
    }

    public function testIfIsValidReturnsFalseWhenTokenDataAreInvalidValidAndTokenIsNotExpired(): void
    {
        $this->validatorMock->shouldReceive('validate')
            ->once()
            ->with($this->jwtMock, ...$this->constraints)
            ->andReturn(false);
        $this->jwtMock->shouldNotReceive('isExpired');

        self::assertFalse($this->jwtValidator->isValid($this->jwtMock));
    }
}
