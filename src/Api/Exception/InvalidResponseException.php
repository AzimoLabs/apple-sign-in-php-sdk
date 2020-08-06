<?php declare(strict_types=1);

namespace Azimo\Apple\Api\Exception;

use InvalidArgumentException;

class InvalidResponseException extends InvalidArgumentException implements AppleApiExceptionInterface
{
}
