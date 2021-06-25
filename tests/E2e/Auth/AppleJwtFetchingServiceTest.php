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
use phpseclib\Crypt\RSA;

final class AppleJwtFetchingServiceTest extends MockeryTestCase
{
    /**
     * @var Auth\Service\AppleJwtFetchingService
     */
    private $appleJwtFetchingService;

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
                new RSA(),
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
            'eyJraWQiOiJZdXlYb1kiLCJhbGciOiJSUzI1NiJ9.eyJpc3MiOiJodHRwczovL2FwcGxlaWQuYXBwbGUuY29tIiwiYXVkIjoiY29tLmMuYXppbW8uc3RhZ2UiLCJleHAiOjE2MjQ2OTI0MTgsImlhdCI6MTYyNDYwNjAxOCwic3ViIjoiMDAwNTYwLjE4MDM2YjI3MmI5MjRkYTg5ZWY3N2RjNDYyNDhkODRhLjA3MjEiLCJjX2hhc2giOiJrZUVNV0dadGg5aDdDRUhIR1dtSVRRIiwiYXV0aF90aW1lIjoxNjI0NjA2MDE4LCJub25jZV9zdXBwb3J0ZWQiOnRydWV9.DQNhKgtNXGffuelanmMT_lnUPbIAVVEiDuj7NL-H4nusxZ5-lK5UBgWhCj79PX1NUxQKj2bOqcb2R2oE2POxIrkpm1jPSt5QXaBeBmwdKx6NtXss2BOq0TL8Jlp7N5UW2TSC2Dk3Cu8-WC-gQf9jgtnzsEkpJFO1G6XrG6ZWCLATDcinN2XRHKzbmxiwNdykUhi1EzH4ug5e0XWchdY5h8QeYqmP0K7SqXZZPxxmcWZC_9g02H58tgChrsYzDezOQxwrf08w7jDXKbaqlpThfh9FMNsMIGaFekhSneOth5_TSc-CPqSHdq3ORRqbiERWcqi0FGJmPnN8-oBVBkcEQA'
        );

        self::assertInstanceOf(Auth\Struct\JwtPayload::class, $jwtPayload);
    }
}
