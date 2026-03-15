<?php

namespace Sosupp\SlimerMetrics\Traits;

trait WithModelMetricData
{
    public $reportData = [];
    public $chartsData = [];

    protected $metrics;

    abstract function getMetricData();

    public function renderedWithModelMetricData()
    {
        $this->chartsData = $this->getMetricData();
    }
}
