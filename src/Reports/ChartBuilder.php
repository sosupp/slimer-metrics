<?php
namespace Sosupp\SlimerMetrics\Reports;

class ChartBuilder
{
    public function build(array $series, array $metrics, array $labels)
    {
        $charts = [];

        // dd($series, $metrics);

        foreach ($metrics as $index => $metric) {

            $dataset = $series['datasets'][$index] ?? [];
            $data = $dataset['data'] ?? [];

            $charts[$metric] = [
                'type' => $dataset['chartType'] ?? 'bar',

                'key' => $metric,

                'labels' => $labels,

                'data' => $data,

                'sum' => [
                    'current' => number_format(array_sum($data), 2),
                    'previous' => number_format($dataset['prev_sum'], 2) ?? 0,
                    'change' => $dataset['pct_change'] ?? null,
                ],

                'count' => [
                    'current' => 0,
                    'previous' => 0,
                    'change' => null,
                ],

                'meta' => $series['meta']

            ];
        }

        return $charts;
    }
}
