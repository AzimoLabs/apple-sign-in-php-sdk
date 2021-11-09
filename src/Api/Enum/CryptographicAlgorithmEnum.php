<?php declare(strict_types=1);

namespace Azimo\Apple\Api\Enum;

class CryptographicAlgorithmEnum
{
    public const KID_86D88KF = '86D88Kf';

    public const KID_EXAUNML = 'eXaunmL';

    public const KID_YUYXOY = 'YuyXoY';

    public static function supportedAlgorithms(): array
    {
        return [
            self::KID_86D88KF,
            self::KID_EXAUNML,
            self::KID_YUYXOY,
        ];
    }

    public static function isSupported(string $algorithm): bool
    {
        return in_array($algorithm, self::supportedAlgorithms(), true);
    }
}
