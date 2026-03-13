<?php

namespace Sosupp\SlimerMetrics\Contracts;

use Sosupp\SlimerMetrics\Engine\MetricCollection;

interface MetricRegistryInterface
{
    public function metrics(): array|MetricCollection;

    public function get(string $metric): ?array;

}
