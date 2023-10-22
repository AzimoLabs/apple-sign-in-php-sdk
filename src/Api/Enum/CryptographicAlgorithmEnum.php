<?php declare(strict_types=1);

namespace Azimo\Apple\Api\Enum;

/**
 * appleid 支持的 auth keys
 * https://appleid.apple.com/auth/keys
 * 之前是2种  86D88Kf eXaunmL
 * 现在是 YuyXoY  fh6Bs8C W6WcOKB
 */
class CryptographicAlgorithmEnum
{
    public const KID_86D88KF = '86D88Kf';

    public const KID_EXAUNML = 'eXaunmL';

    public const KID_YUYXOY = 'YuyXoY';
    // add this two line code
    public const KID_TYPE = 'fh6Bs8C';
    public const KID_TYPE2 = 'W6WcOKB';

    public static function supportedAlgorithms(): array
    {
        return [
            self::KID_86D88KF,
            self::KID_EXAUNML,
            self::KID_YUYXOY,
            // add this two line code
            self::KID_TYPE,
            self::KID_TYPE2,
        ];
    }

    public static function isSupported(string $algorithm): bool
    {
        return in_array($algorithm, self::supportedAlgorithms(), true);
    }
}
