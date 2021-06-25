<?php declare(strict_types=1);

namespace Azimo\Apple\Auth\Exception;

use OutOfBoundsException;

final class MissingClaimException extends OutOfBoundsException implements AppleExceptionInterface
{
}
