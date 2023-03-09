<?php

namespace SynergiTech\Salesforce\Models;

use Stringable;

class Builder implements Stringable
{
    /** @var array<string> $fields */
    protected ?array $fields = null;

    /** @var array<array<string|int|float>> $whereClauses */
    protected array $whereClauses = [];

    /** @var array<string> $orderFields */
    protected array $orderFields = [];

    protected bool $sortAscending = true;
    protected bool $sortNullsFirst = true;
    protected ?int $limit = null;

    public function __construct(protected string $table)
    {
    }

    /**
     * @param string|array<string> $fields
     */
    public function select(string|array $fields): self
    {
        $fields = is_string($fields) ? [$fields] : $fields;
        $this->fields = $fields;

        return $this;
    }

    public function where(string $fieldName, string|int|float $operator, string|int|float|null $value = null): self
    {
        if (!in_array($operator, ['=', '!=', '<', '<=', '>', '>=', 'LIKE'])) {
            $value = $operator;
            $operator = '=';
        }
        $value = $value !== null ? $value : 'null';
        $this->whereClauses[] = [$fieldName, $operator, $this->escapeString($value, "'")];

        return $this;
    }

    /**
     * @param string|array<string|int|float> $values
     */
    public function whereIn(string $fieldName, string|array $values): self
    {
        $values = is_string($values) ? [$values] : $values;
        $values = array_map(fn ($value) => $this->escapeString($value, "'"), $values);
        $this->whereClauses[] = [$fieldName, 'IN', '(' . implode(', ', $values) . ')'];

        return $this;
    }

    /**
     * @param string|array<string> $fields
     */
    public function orderBy(string|array $fields, string $order = 'ASC'): self
    {
        if (is_string($fields)) {
            $this->orderFields[] = $fields;
        } else {
            $this->orderFields = array_merge($this->orderFields, $fields);
        }

        if (strtoupper($order) !== 'ASC') {
            $this->sortAscending = false;
        }

        return $this;
    }

    public function nullsLast(): self
    {
        $this->sortNullsFirst = false;

        return $this;
    }

    public function limit(int $records): self
    {
        $this->limit = $records;

        return $this;
    }

    public function getQuery(): string
    {
        $fields = is_array($this->fields) ? implode(', ', $this->fields) : 'FIELDS(ALL)';
        $query = "SELECT {$fields} FROM {$this->table}";

        if (count($this->whereClauses)) {
            $whereStatements = array_map(fn ($whereClause) => implode(' ', $whereClause), $this->whereClauses);
            $query .= ' WHERE ' . implode(' AND ', $whereStatements);
        }

        if (count($this->orderFields) || !$this->sortNullsFirst) {
            $orderFields = implode(', ', $this->orderFields);
            if ($orderFields) {
                $query .= ' ORDER BY';
                $query .= ' ' . $orderFields;
                $query .= $this->sortAscending ? ' ASC' : ' DESC';
                $query .= $this->sortNullsFirst ? '' : ' NULLS LAST';
            }
        }

        if ($this->limit) {
            $query .= ' LIMIT ' . $this->limit;
        }

        return $query;
    }

    protected function escapeString(string|int|float $value, string $wrap = ''): string|int|float
    {
        return is_string($value) ? $wrap . str_replace("'", "\\'", $value) . $wrap : $value;
    }

    public function __toString(): string
    {
        return $this->getQuery();
    }
}
