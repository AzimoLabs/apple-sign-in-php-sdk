<?php declare(strict_types=1);

namespace Azimo\Apple\Auth\Struct;

use DateTimeInterface;

final class JwtPayload
{
    private string $iss;

    private array $aud;

    private DateTimeInterface $exp;

    private DateTimeInterface $iat;

    private string $sub;

    private string $cHash;

    private string $email;

    private bool $emailVerified;

    private bool $isPrivateEmail;

    private int $authTime;

    private bool $nonceSupported;

    public function __construct(
        string $iss,
        array $aud,
        DateTimeInterface $exp,
        DateTimeInterface $iat,
        string $sub,
        string $cHash,
        string $email,
        bool $emailVerified,
        bool $isPrivateEmail,
        int $authTime,
        bool $nonceSupported
    ) {
        $this->iss = $iss;
        $this->aud = $aud;
        $this->exp = $exp;
        $this->iat = $iat;
        $this->sub = $sub;
        $this->cHash = $cHash;
        $this->email = $email;
        $this->emailVerified = $emailVerified;
        $this->isPrivateEmail = $isPrivateEmail;
        $this->authTime = $authTime;
        $this->nonceSupported = $nonceSupported;
    }

    public function getIss(): string
    {
        return $this->iss;
    }

    public function getAud(): array
    {
        return $this->aud;
    }

    public function getExp(): DateTimeInterface
    {
        return $this->exp;
    }

    public function getIat(): DateTimeInterface
    {
        return $this->iat;
    }

    public function getSub(): string
    {
        return $this->sub;
    }

    public function getCHash(): string
    {
        return $this->cHash;
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

    public function getAuthTime(): int
    {
        return $this->authTime;
    }

    public function isNonceSupported(): bool
    {
        return $this->nonceSupported;
    }
}
