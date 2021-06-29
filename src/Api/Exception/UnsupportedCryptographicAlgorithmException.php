<?php declare(strict_types=1);

namespace Azimo\Apple\Api\Exception;

use InvalidArgumentException;

final class UnsupportedCryptographicAlgorithmException extends InvalidArgumentException implements AppleApiExceptionInterface
{
}
