<?php declare(strict_types=1);

namespace Azimo\Apple\Auth\Exception;

use RuntimeException;

final class KeysFetchingFailedException extends RuntimeException implements AppleExceptionInterface
{
}
