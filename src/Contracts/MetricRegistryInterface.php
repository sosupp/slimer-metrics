<?php

namespace Sosupp\SlimerMetrics\Contracts;

interface MetricRegistryInterface
{
    public function metrics(): array;

    public function get(string $metric): ?array;

}