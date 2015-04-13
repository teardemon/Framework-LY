<?php

/**
 * Created by PhpStorm.
 * Author: LyonWong
 * Date: 2014-08-07
 */
class routerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param $locator
     * @param $expected
     * @dataProvider dataParseLocator
     */
    public function testParseLocator($locator, $expected)
    {
        $actual = router::parseLocator("CTL/$locator");
        $this->assertSame($expected, $actual, "parseLocator failed `$locator`");
    }

    public function dataParseLocator()
    {
        $data = [
            [
                '/Web/',
                [
                    'control' => 'CTL\Web\index',
                    'method' => 'index',
                    'params' => [],
                ],
            ],
            [
                '/Web/test',
                [
                    'control' => 'CTL\Web\test',
                    'method' => 'index',
                    'params' => [],
                ]
            ],
            [
                '/cron/',
                [
                    'control' => 'CTL\cron\index',
                    'method' => 'index',
                    'params' => [],
                ]
            ],
            [
                '/cron/test-foo-a-b',
                [
                    'control' => 'CTL\cron\test',
                    'method' => 'foo',
                    'params' => ['a','b'],
                ]
            ]
        ];
        return $data;
    }


}
 