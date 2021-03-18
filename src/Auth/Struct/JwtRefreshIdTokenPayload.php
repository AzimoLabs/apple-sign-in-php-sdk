<?php declare(strict_types=1);

namespace Azimo\Apple\Auth\Struct;

class JwtRefreshIdTokenPayload
{
    /**
     * @var string
     */
    private $iss;

    /**
     * @var string
     */
    private $aud;

    /**
     * @var int
     */
    private $exp;

    /**
     * @var int
     */
    private $iat;

    /**
     * @var string
     */
    private $sub;

    /**
     * @var string
     */
    private $at_Hash;

    /**
     * @var string
     */
    private $email;

    /**
     * @var bool
     */
    private $emailVerified;

    /**
     * @var bool
     */
    private $isPrivateEmail;

    /**
     * @var bool
     */
    private $nonceSupported;

    public function __construct(
        string $iss,
        string $aud,
        int $exp,
        int $iat,
        string $sub,
        string $at_Hash,
        string $email,
        bool $emailVerified,
        bool $isPrivateEmail,
        bool $nonceSupported
    ) {
        $this->iss = $iss;
        $this->aud = $aud;
        $this->exp = $exp;
        $this->iat = $iat;
        $this->sub = $sub;
        $this->at_Hash = $at_Hash;
        $this->email = $email;
        $this->emailVerified = $emailVerified;
        $this->isPrivateEmail = $isPrivateEmail;
        $this->nonceSupported = $nonceSupported;
    }

    public function getIss(): string
    {
        return $this->iss;
    }

    public function getAud(): string
    {
        return $this->aud;
    }

    public function getExp(): int
    {
        return $this->exp;
    }

    public function getIat(): int
    {
        return $this->iat;
    }

    public function getSub(): string
    {
        return $this->sub;
    }

    public function getAtHash(): string
    {
        return $this->at_Hash;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function isEmailVerified(): bool
    {
        return $this->emailVerified;
    }

    public function isPrivateEmail(): bool
    {
        return $this->isPrivateEmail;
    }

    public function isNonceSupported(): bool
    {
        return $this->nonceSupported;
    }
}
