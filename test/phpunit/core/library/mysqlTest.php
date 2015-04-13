<?php
/**
 * Created by PhpStorm.
 * Author: LyonWong
 * Date: 2014-08-30
 */

namespace Core\Library;


class mysqlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var mysql
     */
    protected static $mysql;

    protected static $table;

    public static function setUpBeforeClass()
    {
        self::$mysql = mysql::instance('test');
        self::$table = $table = "test_" . time();
        $tableTest = "
            create table $table (
                `id` int not null auto_increment,
                `key` varchar(64) not null,
                `name` varchar(255) not null,
                `value` varchar(255) not null default '',
                `content` text,
                primary key (id)
            );
        ";
        self::$mysql->run($tableTest);
    }

    public static function tearDownAfterClass()
    {
        self::$mysql->drop(self::$table);
    }

    public function setUp()
    {
        $fields = ['key', 'name', 'value'];
        $values = [
            ['k1', 'n1', 'v1'],
            ['k2', 'n2', 'v2'],
            ['k3', 'n3', 'v3'],
        ];
        self::$mysql->insert(self::$table, null)->values($fields, $values)->run();
    }

    public function tearDown()
    {
        self::$mysql->run("truncate table " . self::$table);
    }


    public function testInsert()
    {
        $data = [
            'key' => 'k1',
            'name' => 'key1',
            'value' => 'v1',
        ];
        $rowCount = self::$mysql->insert(self::$table, $data)->run()->rowCount();
        $this->assertSame(1, $rowCount);
    }

    public function testInsertBatch()
    {
        $fields = ['key', 'name', 'value'];
        $values = [
            ['1', 'a', 'A'],
            ['2', 'b', 'B'],
        ];
        $rowCount = self::$mysql->insert(self::$table, null)->values($fields, $values)->rowCount();
        $this->assertEquals(count($values), $rowCount, 'insertBatch rowCount not match');
    }

    public function testSelect()
    {
        $expected = [
            'id' => 1,
            'key' => 'k1',
            'name' => 'n1',
            'value' => 'v1',
            'content' => '',
        ];
        $actual = self::$mysql->select(self::$table)->fetch();
        $this->assertEquals($expected, $actual, "select not match");
    }


    public function testWhere()
    {
        $expected = [
            'key' => 'k1',
            'value' => 'v1',
        ];

        $actual1 = self::$mysql->select(self::$table, ['key', 'value'])->where("name='n1'")->limit(1)->fetch();
        $this->assertSame($expected, $actual1, "`Where` by string.");

        $actual2 = self::$mysql->select(self::$table, ['key', 'value'])->where(['name' => 'n1'])->limit(1)->fetch();
        $this->assertSame($expected, $actual2, "`Where` by equal.");

        $actual3 = self::$mysql->select(self::$table, ['key', 'value'])->where(['name like ?' => ['n1%']])->fetch();
        $this->assertSame($expected, $actual3, "`Where` by bind.");
    }

    public function testUpdate()
    {
        $data = [
            'value' => 'newValue'
        ];
        self::$mysql->update(self::$table, $data)->where(['name' => 'n1'])->run();
        $actual = self::$mysql->select(self::$table, ['value'])->where(['name' => 'n1'])->fetch();
        $this->assertSame($data, $actual, "`update`");
    }

    public function testIn()
    {
        $nameSet = ['n1', 'n2'];
        $actual = self::$mysql->select(self::$table)->where('name')->in($nameSet)->fetchAll(null, 'name');
        $this->assertSame($nameSet, $actual, "`in`");
    }

    public function testDelete()
    {
        $rowCount = self::$mysql->delete(self::$table, ['id' => 1])->rowCount();
        $this->assertEquals(1, $rowCount, "`delete` rowCount don't match'");
        $restCount = self::$mysql->select(self::$table, 'count(*)')->where(['id' => 1])->fetch(1);
        $this->assertEquals(0, $restCount, "`delete` rest don't match");
    }

    public function testFetch()
    {
        $res = self::$mysql->select(self::$table, ['id', 'key', 'value'])->run();

        $fetch1 = $res->fetch();
        $expected1 = [
            'id' => '1',
            'key' => 'k1',
            'value' => 'v1',
        ];
        $this->assertEquals($expected1, $fetch1, "`fetch` as row.");

        $fetch2 = $res->fetch('value');
        $this->assertSame("v2", $fetch2, "`fetch` by column name.");

        $fetch3 = $res->fetch(1);
        $this->assertEquals("3", $fetch3, "`fetch` by column offset.");
    }

    public function testFetchAll()
    {
        $fetch1 = self::$mysql->select(self::$table, ['id', 'key'])->fetchAll();
        $expected1 = [
            [
                'id' => '1',
                'key' => 'k1',
            ],
            [
                'id' => '2',
                'key' => 'k2',
            ],
            [
                'id' => '3',
                'key' => 'k3',
            ]
        ];
        $this->assertEquals($expected1, $fetch1, "`fetch all`.");

        $fetch2 = self::$mysql->select(self::$table, ['id', 'key'])->fetchAll('key');
        $expected2 = [
            'k1' => [
                'id' => '1',
                'key' => 'k1',
            ],
            'k2' => [
                'id' => '2',
                'key' => 'k2',
            ],
            'k3' => [
                'id' => '3',
                'key' => 'k3',
            ]
        ];
        $this->assertEquals($expected2, $fetch2, "`fetch all` with key.");

        $fetch3 = self::$mysql->select(self::$table, ['id', 'key'])->fetchAll('id', 'key');
        $expected3 = [
            '1' => 'k1',
            '2' => 'k2',
            '3' => 'k3',
        ];
        $this->assertEquals($expected3, $fetch3, "`fetch all` by column name as key-value.");
        $fetch3 = self::$mysql->select(self::$table, ['id', 'key'])->fetchAll(1, 2);
        $this->assertSame($expected3, $fetch3, "`fetch all` by offset as key-value.");

        $fetch4 = self::$mysql->select(self::$table, ['id', 'key'])->fetchAll(null, 'key');
        $expected4 = [
            'k1', 'k2', 'k3'
        ];
        $this->assertEquals($expected4, $fetch4, "`fetch all` as column.");
    }

    public function testOrderBy()
    {
        $actual = self::$mysql->select(self::$table, ['key'])->orderBy('value', 'DESC')->fetchAll(null, 'key');
        $expected = [
            'k3', 'k2', 'k1'
        ];
        $this->assertSame($expected, $actual, "order by");
    }

    public function testGroupBy()
    {
        $data = [
            'key' => 'k4',
            'name' => 'n4',
            'value' => 'v2',
        ];
        self::$mysql->insert(self::$table, $data)->run();
        $actual = self::$mysql->select(self::$table, '`value`,count(*) as cnt')->groupBy('value')->fetchAll(1, 2);
        $expected = [
            'v1' => '1',
            'v2' => '2',
            'v3' => '1'
        ];
        $this->assertEquals($expected, $actual, "group by");
    }

}
 