<?php declare(strict_types=1);

namespace Azimo\Apple\Tests\Unit\Api;

use Azimo\Apple\Api\Utils;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

final class UtilsTest extends TestCase
{
    public function testDecodesJson()
    {
        $this->assertTrue(Utils::jsonDecode('true'));
        $this->assertEquals(['a' => 1, 'b' => 2], Utils::jsonDecode('{"a":1,"b":2}', true));
        $this->assertEquals((object) ['a' => 1, 'b' => 2], Utils::jsonDecode('{"a":1,"b":2}'));
        $this->assertEquals([5, 10], Utils::jsonDecode('[5, 10]', true));
        $this->assertEquals([5, 10], Utils::jsonDecode('[5, 10]'));
    }

    public function testDecodesJsonAndThrowsOnError()
    {
        $this->expectException(InvalidArgumentException::class);

        Utils::jsonDecode('{{]]');
    }
}
