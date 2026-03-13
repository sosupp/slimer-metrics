<?php
namespace Sosupp\SlimerMetrics\Charts;

interface ChartAdapterInterface
{
    public function format(array $charts): array;
}