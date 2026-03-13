<?php
namespace Sosupp\SlimerMetrics\Charts;

class ChartJSAdapter implements ChartAdapterInterface
{
    public function format(array $charts): array
    {
        $formatted = [];

        foreach ($charts as $chart) {

            $formatted[] = [

                'labels' => $chart['labels'],

                'datasets' => [
                    [
                        'label' => $chart['key'],
                        'data' => $chart['data'],
                        'backgroundColor' => $chart['color'] ?? 'rgba(75,192,192,0.6)'
                    ]
                ]
            ];
        }

        return $formatted;
    }
}