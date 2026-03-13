<?php

namespace Sosupp\SlimerMetrics\Engine;

class MetricCollection
{
    protected $metrics = [];

    public function add(Metric $metric)
    {
        $this->metrics[$metric->toArray()['name']] = $metric;

        return $this;
    }

    public function all()
    {
        return $this->metrics;
    }

    public function get($name)
    {
        return $this->metrics[$name] ?? null;
    }
}