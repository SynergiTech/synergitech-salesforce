<?php

namespace SynergiTech\Salesforce\Tests;

use Orchestra\Testbench\TestCase;
use SynergiTech\Salesforce\Models\Builder;

class BuilderTest extends TestCase
{
    protected string $tableName = 'Test';

    public function testDefaultQuery(): void
    {
        $expectedQuery = "SELECT FIELDS(ALL) FROM {$this->tableName}";

        $builder = new Builder($this->tableName);
        $this->assertEquals($expectedQuery, $builder->getQuery());
    }

    public function testSelect(): void
    {
        // Test that select works with a single field
        $expectedQuery = "SELECT Id FROM {$this->tableName}";
        $builder = new Builder($this->tableName);
        $builder->select('Id');
        $this->assertEquals($expectedQuery, $builder->getQuery());

        // Test that select works with multiple fields
        $expectedQuery = "SELECT Id, Name FROM {$this->tableName}";
        $builder = new Builder($this->tableName);
        $builder->select(['Id', 'Name']);
        $this->assertEquals($expectedQuery, $builder->getQuery());
    }

    public function testWhere(): void
    {
        // Test that where works with a string
        $expectedQuery = "SELECT FIELDS(ALL) FROM {$this->tableName} WHERE field1 = 'something'";
        $builder = new Builder($this->tableName);
        $builder->where('field1', 'something');
        $this->assertEquals($expectedQuery, $builder->getQuery());

        // Test that where works with an integer
        $expectedQuery = "SELECT FIELDS(ALL) FROM {$this->tableName} WHERE field1 = 72";
        $builder = new Builder($this->tableName);
        $builder->where('field1', 72);
        $this->assertEquals($expectedQuery, $builder->getQuery());

        // Test that where works with a mix of types
        $expectedQuery = "SELECT FIELDS(ALL) FROM {$this->tableName} WHERE field1 = 15 AND field2 = 'somethingelse'";
        $builder = new Builder($this->tableName);
        $builder->where('field1', 15);
        $builder->where('field2', 'somethingelse');
        $this->assertEquals($expectedQuery, $builder->getQuery());

        // Test that where works when chaining
        $expectedQuery =
            "SELECT FIELDS(ALL) FROM {$this->tableName} WHERE field1 = 36 AND field2 = '32' AND field3 = '22'";
        $builder = new Builder($this->tableName);
        $builder->where('field1', 36)->where('field2', '32')->where('field3', '22');
        $this->assertEquals($expectedQuery, $builder->getQuery());

        // Test that where works with all operators
        $operators = ['=', '!=', '<', '<=', '>', '>=', 'LIKE'];
        foreach ($operators as $operator) {
            $rand = mt_rand(-1000, 1000);
            $expectedQuery = "SELECT FIELDS(ALL) FROM {$this->tableName} WHERE field {$operator} {$rand}";
            $builder = new Builder($this->tableName);
            $builder->where('field', $operator, $rand);
            $this->assertEquals($expectedQuery, $builder->getQuery());
        }
    }

    public function testWhereIn(): void
    {
        // Test that whereIn works with a single value
        $expectedQuery = "SELECT FIELDS(ALL) FROM {$this->tableName} WHERE field IN ('1')";
        $builder = new Builder($this->tableName);
        $builder->whereIn('field', '1');
        $this->assertEquals($expectedQuery, $builder->getQuery());

        // Test that whereIn works with a multiple values
        $expectedQuery = "SELECT FIELDS(ALL) FROM {$this->tableName} WHERE field IN ('1', '2', '3')";
        $builder = new Builder($this->tableName);
        $builder->whereIn('field', ['1', '2', '3']);
        $this->assertEquals($expectedQuery, $builder->getQuery());

        // Test that whereIn works with integer values
        $expectedQuery = "SELECT FIELDS(ALL) FROM {$this->tableName} WHERE field IN (1, 2, 3)";
        $builder = new Builder($this->tableName);
        $builder->whereIn('field', [1, 2, 3]);
        $this->assertEquals($expectedQuery, $builder->getQuery());
    }

    public function testOrderBy(): void
    {
        // Test that orderBy works without specifying an order
        $expectedQuery = "SELECT FIELDS(ALL) FROM {$this->tableName} ORDER BY date ASC";
        $builder = new Builder($this->tableName);
        $builder->orderBy('date');
        $this->assertEquals($expectedQuery, $builder->getQuery());

        // Test that orderBy works in descending order
        $expectedQuery = "SELECT FIELDS(ALL) FROM {$this->tableName} ORDER BY date DESC";
        $builder = new Builder($this->tableName);
        $builder->orderBy('date', 'DESC');
        $this->assertEquals($expectedQuery, $builder->getQuery());

        // Test that orderBy works with multiple values
        $expectedQuery = "SELECT FIELDS(ALL) FROM {$this->tableName} ORDER BY date1, date2 ASC";
        $builder = new Builder($this->tableName);
        $builder->orderBy(['date1', 'date2']);
        $this->assertEquals($expectedQuery, $builder->getQuery());
    }

    public function testNullsLast(): void
    {
        // Test that nulls last doesn't append without an orderBy clause
        $expectedQuery = "SELECT FIELDS(ALL) FROM {$this->tableName}";
        $builder = new Builder($this->tableName);
        $builder->nullsLast();
        $this->assertEquals($expectedQuery, $builder->getQuery());

        // Test that nulls last appends with an orderBy clause
        $expectedQuery = "SELECT FIELDS(ALL) FROM {$this->tableName} ORDER BY date ASC NULLS LAST";
        $builder = new Builder($this->tableName);
        $builder->orderBy('date')->nullsLast();
        $this->assertEquals($expectedQuery, $builder->getQuery());
    }

    public function testEscapeString(): void
    {
        // Test that apostrophes are correctly escaped in field values
        $expectedQuery = "SELECT FIELDS(ALL) FROM {$this->tableName} WHERE field LIKE 'It\\'s'";
        $builder = new BUILDER($this->tableName);
        $builder->where('field', 'LIKE', "It's");
        $this->assertEquals($expectedQuery, $builder->getQuery());
    }
}
