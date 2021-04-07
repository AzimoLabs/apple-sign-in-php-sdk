<?php declare(strict_types=1);

namespace Azimo\Apple\Tests\E2eRefreshToken\Auth;

use Azimo\Apple\Api;
use Azimo\Apple\Auth;
use GuzzleHttp;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\ValidationData;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use phpseclib\Crypt\RSA;

class AppleJwtFetchingServiceTest extends MockeryTestCase
{
    /**
     * @var Auth\Service\AppleJwtFetchingService
     */
    private $appleJwtFetchingService;

    public function setUp(): void
    {
        parent::setUp();

        $validationData = new ValidationData();
        $validationData->setIssuer('https://appleid.apple.com');
        $validationData->setAudience('service.com.hivegroupinc.cardxchange');

        $this->appleJwtFetchingService = new Auth\Service\AppleJwtFetchingService(
            new Auth\Jwt\JwtParser(new Parser()),
            new Auth\Jwt\JwtVerifier(
                new Api\AppleApiClient(
                    new GuzzleHttp\Client(
                        [
                            'base_uri'        => 'https://appleid.apple.com',
                            'timeout'         => 5,
                            'connect_timeout' => 5,
                        ]
                    ),
                    new Api\Factory\ResponseFactory()
                ),
                new RSA(),
                new Sha256()
            ),
            new Auth\Jwt\JwtValidator($validationData),
            new Auth\Factory\AppleJwtStructFactory()
        );
    }

    public function testIfGetJwtPayloadReturnExpectedJwtPayloadFromRefreshToken(): void
    {
        $jwtPayload = $this->appleJwtFetchingService->getRefreshJwtPayload(
            'eyJraWQiOiJlWGF1bm1MIiwiYWxnIjoiUlMyNTYifQ.eyJpc3MiOiJodHRwczovL2FwcGxlaWQuYXBwbGUuY29tIiwiYXVkIjoic2VydmljZS5jb20uaGl2ZWdyb3VwaW5jLmNhcmR4Y2hhbmdlIiwiZXhwIjoxNjE2NTAzNjQyLCJpYXQiOjE2MTY0MTcyNDIsInN1YiI6IjAwMDU4MS43ZDUyN2M2ZDUwMzI0NDM5YTUxMmVlN2ZhYmZjY2FjNy4xMjQ2IiwiYXRfaGFzaCI6InJMdHpob3JSN18wa25Fa0pCeGJqckEiLCJlbWFpbCI6InBnNXNndng1Mm5AcHJpdmF0ZXJlbGF5LmFwcGxlaWQuY29tIiwiZW1haWxfdmVyaWZpZWQiOiJ0cnVlIiwiaXNfcHJpdmF0ZV9lbWFpbCI6InRydWUifQ.S7VWp-1pfaF1jNBYBW1GBsDO6ZT_ois5IjWmtqdGfknZT6ijCmmRI6cWKvbUo4K1herU47wbaHQR3WYCSkbkSBS9pu0zLiK1D6Y_Yb59ZJG4HkCWnscv1yIe4RcXvcRpx_i5f036NiOLbq-8HSn0_2XSC8N7KWacrIQ90LbPINzlq8fSwLJMGx0_JvjK0h-hRSDnr-Z-0bjmLYJcGP2D-l5lX39S16CA7nfICx3BWVgKuHqJo_8VW2E8CFuER8pJI_mIn3DGOsX4UpD6mcjTHasXZ-qO4RNiIow9xcvG_gnqlVTwaEId-bc8N5QJB3uX3oGM6P7k5FINLntYKaN-Ng'
        );

        self::assertInstanceOf(Auth\Struct\JwtRefreshIdTokenPayload::class, $jwtPayload);
    }
}
