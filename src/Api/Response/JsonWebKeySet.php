<?php declare(strict_types=1);

namespace Azimo\Apple\Api\Response;

class JsonWebKeySet
{
    /**
     * The family of cryptographic algorithms used with the key.
     */
    private string $kty;

    /**
     * The unique identifier for the key.
     */
    private string $kid;

    /**
     * How the key was meant to be used; `sig` represents the signature.
     */
    private string $use;

    /**
     * The specific cryptographic algorithm used with the key.
     */
    private string $alg;

    /**
     * The modulus for the RSA public key.
     */
    private string $modulus;

    /**
     * The exponent for the RSA public key.
     */
    private string $exponent;

    public function __construct(string $kty, string $kid, string $use, string $alg, string $modulus, string $exponent)
    {
        $this->kty = $kty;
        $this->kid = $kid;
        $this->use = $use;
        $this->alg = $alg;
        $this->modulus = $modulus;
        $this->exponent = $exponent;
    }

    public function getKty(): string
    {
        return $this->kty;
    }

    public function getKid(): string
    {
        return $this->kid;
    }

    public function getUse(): string
    {
        return $this->use;
    }

    public function getAlg(): string
    {
        return $this->alg;
    }

    public function getModulus(): string
    {
        return $this->modulus;
    }

    public function getExponent(): string
    {
        return $this->exponent;
    }
}
