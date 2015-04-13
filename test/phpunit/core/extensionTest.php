<?php
/**
 * Created by PhpStorm.
 * Author: LyonWong
 * Date: 2014-08-30
 */

class extensionTest extends PHPUnit_Framework_TestCase
{
    public function testArrayFetch()
    {
        $this->assertSame(false, arrayFetch(null, 0), 'Fetch with no-array input');
        $array = [
            'a'=>'A',
            'b'=>'B',
        ];
        $itemA = arrayFetch($array, 0);
        $itemB = arrayFetch($array, 'b');
        $this->assertSame('A', $itemA, "Fetch by index failed.");
        $this->assertSame('B', $itemB, "Fetch by assoc failed.");
    }

    public function testArrayMergeForce()
    {
        $array = [
            '1' => '1',
            'a'=>'A',
            'b'=>['b'],
            'c'=>[
                'cc'=>'CC',
            ]
        ];
        $array1 = [
            '1'=>11,
            'b' => 'BB',
        ];
        $actual1 = arrayMergeForce($array, $array1);
        $expected1 = [
            '1'=>11,
            'a'=>'A',
            'b'=>'BB',
            'c'=>[
                'cc'=>'CC',
            ]
        ];
        $this->assertSame($expected1, $actual1, "Force merge failed.");
        $array2 = [
            'c'=>[
                'cc'=>'CCC',
            ]
        ];
        $actual2 = arrayMergeForce($array, $array1, $array2);
        $expected2 = [
            '1'=>11,
            'a'=>'A',
            'b'=>'BB',
            'c'=>[
                'cc'=>'CCC',
            ]
        ];
        $this->assertSame($expected2, $actual2, "Recursively force merge failed.");
    }
}
 