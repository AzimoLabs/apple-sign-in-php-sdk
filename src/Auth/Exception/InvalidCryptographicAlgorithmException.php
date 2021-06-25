<?php declare(strict_types=1);

namespace Azimo\Apple\Auth\Exception;

use InvalidArgumentException;

final class InvalidCryptographicAlgorithmException extends InvalidArgumentException implements AppleExceptionInterface
{
}
