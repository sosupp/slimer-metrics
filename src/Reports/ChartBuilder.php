<?php
namespace Sosupp\SlimerMetrics\Reports;

class ChartBuilder
{
    public function build(array $series, array $metrics, array $labels)
    {
        $charts = [];

        foreach ($metrics as $index => $metric) {

            $dataset = $series['datasets'][$index] ?? [];
            $data = $dataset['data'] ?? [];

            $charts[$metric] = [

                'key' => $metric,

                'labels' => $labels,

                'data' => $data,

                'sum' => array_sum($data),

                'prev_sum' => $dataset['prev_sum'] ?? 0,

                'change' => $dataset['pct_change'] ?? null

            ];
        }

        return $charts;
    }
}