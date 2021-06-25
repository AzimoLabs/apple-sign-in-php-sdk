<?php declare(strict_types=1);

namespace Azimo\Apple\Api\Exception;

use InvalidArgumentException;

final class InvalidResponseException extends InvalidArgumentException implements AppleApiExceptionInterface
{
}
