<?php
namespace Sosupp\SlimerMetrics\Engine;

class Metric
{
    protected $name;
    protected $table;
    protected $column;
    protected $type = 'sum';
    protected $wheres = [];
    protected $expression = null;
    public $chartType = 'bar';

    public static function make(string $name)
    {
        $instance = new static();
        $instance->name = $name;

        return $instance;
    }

    public function table(string $table)
    {
        $this->table = $table;
        return $this;
    }

    public function column(string $column)
    {
        $this->column = $column;
        return $this;
    }

    public function sum()
    {
        $this->type = 'sum';
        return $this;
    }

    public function count()
    {
        $this->type = 'count';
        return $this;
    }

    public function where($column, $value)
    {
        $this->wheres[$column] = $value;
        return $this;
    }

    public function expression(string $expression)
    {
        $this->expression = $expression;

        return $this;
    }

    public function chartType(string $chartType)
    {
        $this->chartType = $chartType;

        return $this;
    }

    public function isComputed(): bool
    {
        return !is_null($this->expression);
    }

    public function expressionValue()
    {
        return $this->expression;
    }

    public function toArray()
    {
        return [
            'name' => $this->name,
            'table' => $this->table,
            'column' => $this->column,
            'type' => $this->type,
            'where' => $this->wheres,
            'chartType' => $this->chartType,
        ];
    }
}
