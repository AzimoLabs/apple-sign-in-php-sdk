<?php declare(strict_types=1);

namespace Azimo\Apple\Auth\Exception;

use RuntimeException;

class KeysFetchingFailedException extends RuntimeException implements AppleExceptionInterface
{
}
