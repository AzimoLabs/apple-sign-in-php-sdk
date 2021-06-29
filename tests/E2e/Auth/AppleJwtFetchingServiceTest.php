<?php declare(strict_types=1);

namespace Azimo\Apple\Tests\E2e\Auth;

use Azimo\Apple\Api;
use Azimo\Apple\Auth;
use GuzzleHttp;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Validator;
use Mockery\Adapter\Phpunit\MockeryTestCase;

final class AppleJwtFetchingServiceTest extends MockeryTestCase
{
    private Auth\Service\AppleJwtFetchingService $appleJwtFetchingService;

    public function setUp(): void
    {
        parent::setUp();

        $this->appleJwtFetchingService = new Auth\Service\AppleJwtFetchingService(
            new Auth\Jwt\JwtParser(new Parser(new JoseEncoder())),
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
                new Validator(),
                new Sha256()
            ),
            new Auth\Jwt\JwtValidator(
                new Validator(),
                [
                    new IssuedBy('https://appleid.apple.com'),
                    new PermittedFor('com.c.azimo.stage'),
                ]
            ),
            new Auth\Factory\AppleJwtStructFactory()
        );
    }

    public function testIfGetJwtPayloadReturnExpectedJwtPayload(): void
    {
        $jwtPayload = $this->appleJwtFetchingService->getJwtPayload(
            'eyJraWQiOiJZdXlYb1kiLCJhbGciOiJSUzI1NiJ9.eyJpc3MiOiJodHRwczovL2FwcGxlaWQuYXBwbGUuY29tIiwiYXVkIjoiY29tLmMuYXppbW8uc3RhZ2UiLCJleHAiOjE2MjQ3MDE3MzUsImlhdCI6MTYyNDYxNTMzNSwic3ViIjoiMDAwNTYwLjE4MDM2YjI3MmI5MjRkYTg5ZWY3N2RjNDYyNDhkODRhLjA3MjEiLCJjX2hhc2giOiJzQWhpVmFTYXlKNlRSVFdoWFMxdGFBIiwiYXV0aF90aW1lIjoxNjI0NjE1MzM1LCJub25jZV9zdXBwb3J0ZWQiOnRydWV9.osvYd0hNosZKWD85-CJmyNXivFgWhrNCOdOpiB7VuYsRRMFn5cxZCFg8fBEiaekeVtHXMsilxoE7FUKfyZ14smi7QNk87qAcZ_ivF52x_l6hkR0YCANdbcIrJqFJQ2GwL1DHN4hE628qEZBf_dj5SdTcixHM-8X3ibWDt4irzBACiXqWvbaeRqFdhwJl-yG_of-9zjYg98-Hlk18MphxCgqmVhFXlOi_al4sVHdqZtUjMgGyqszmoIURgU9lOXXDGKZ3LyBU7vXJIZY4FjcXsOtJ4PO8N2LD_2EN2hXRdiUV_A86Dki_O9w17ZjYxlLwpsxRm_m3SzTVptgL0DL_7Q'
        );

        self::assertInstanceOf(Auth\Struct\JwtPayload::class, $jwtPayload);
    }
}
