<?php
namespace Sosupp\SlimerMetrics\Engine;

class ComputedMetricEvaluator
{
    public function evaluate($expression, $values)
    {
        foreach ($values as $metric => $value) {
            $expression = str_replace($metric, $value, $expression);
        }

        return eval("return {$expression};");
    }
}