<?php declare(strict_types=1);

namespace Tests\E2e\Azimo\Apple\Auth\Service;

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
        $validationData->setAudience('com.c.azimo.stage');

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

    public function testIfGetJwtPayloadReturnExpectedJwtPayload(): void
    {
        $jwtPayload = $this->appleJwtFetchingService->getJwtPayload(
            'eyJraWQiOiI4NkQ4OEtmIiwiYWxnIjoiUlMyNTYifQ.eyJpc3MiOiJodHRwczovL2FwcGxlaWQuYXBwbGUuY29tIiwiYXVkIjoiY29tLmMuYXppbW8uc3RhZ2UiLCJleHAiOjE1OTQ0MTQ5NDMsImlhdCI6MTU5NDQxNDM0Mywic3ViIjoiMDAwNTYwLjE4MDM2YjI3MmI5MjRkYTg5ZWY3N2RjNDYyNDhkODRhLjA3MjEiLCJjX2hhc2giOiJGR01iTERMZXQyOW1qLUFGODNfejVBIiwiYXV0aF90aW1lIjoxNTk0NDE0MzQzLCJub25jZV9zdXBwb3J0ZWQiOnRydWV9.V_zRNOW2OCQDAomXNBtdZn6apjuYapUvV0AtT6Shg6n2SVUSg_Ei0Xf3oz-nuxU-PTHUbyg2Lf4G0egAZN-z1f_h4aia_sqG4eCpK1ZEA97I93udNNm9y0cUBvkXtPxvhw1Mtu8p231OVlE9muDRY_TV5YmY-9spLdN6hX5UHw0Qq4p4RZnTPc7E6HYclh4uf3PdgvE3OD6OKjDNU_gx2TldzCRN5NZ4qcVH-hEE-y7tulvI4df8YMhUaNgbxRYJ62nz67UFFbbgORB1sHMaQon6YFNWsjpYKF8nq6Id1GR-ZCqDAd4QdpZJ-BvD38p5o-uDNw-S27e9Bd0j1d43fw'
        );

        self::assertInstanceOf(Auth\Struct\JwtPayload::class, $jwtPayload);
    }
}
