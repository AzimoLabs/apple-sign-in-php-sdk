<?php declare(strict_types=1);

namespace Azimo\Apple\Api\Exception;

use RuntimeException;

class PublicKeyFetchingFailedException extends RuntimeException implements AppleApiExceptionInterface
{
}
