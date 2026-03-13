<?php

namespace Sosupp\SlimerMetrics\Engine;

class MetricDependencyResolver
{
    public function resolve(array $metrics)
    {
        $resolved = [];

        foreach ($metrics as $metric) {

            if ($metric->isComputed()) {

                preg_match_all('/[a-zA-Z_]+/', $metric->expressionValue(), $matches);

                $resolved[$metric->toArray()['name']] = $matches[0];

            } else {
                $resolved[$metric->toArray()['name']] = [];
            }
        }

        return $resolved;
    }
}