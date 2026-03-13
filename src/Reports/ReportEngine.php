<?php
namespace Sosupp\SlimerMetrics\Reports;

use Sosupp\SlimerMetrics\Charts\ChartAdapterInterface;
use Sosupp\SlimerMetrics\Contracts\MetricRegistryInterface;
use Sosupp\SlimerMetrics\Engine\ComputedMetricEvaluator;
use Sosupp\SlimerMetrics\Engine\MetricQueryBuilder;

class ReportEngine
{
    protected $registry;

    protected $metrics = [];

    protected $date;

    protected $groupBy;

    protected $adapter;

    public function registry(MetricRegistryInterface $registry)
    {
        $this->registry = $registry;

        return $this;
    }

    public function metrics(array $metrics)
    {
        $this->metrics = $metrics;

        return $this;
    }

    public function forDate($date)
    {
        $this->date = $date;

        return $this;
    }

    public function groupBy($groupBy)
    {
        $this->groupBy = $groupBy;

        return $this;
    }

    public function adapter(ChartAdapterInterface $adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }

    public function metricsFromRegistry()
    {
        $collection = $this->registry->metrics();

        $this->metrics = array_keys(
            array_map(
                fn($metric) => $metric->toArray(),
                $collection->all()
            )
        );

        return $this;
    }

    public function generate()
    {
        $builder = new MetricQueryBuilder($this->registry);

        $datasets = [];
        $metricValues = [];

        foreach ($this->metrics as $metricName) {
            $metric = $this->registry->get($metricName);

            if ($metric->isComputed()) {
                continue;
            }

            $query = $builder->build($metricName, $this->groupBy);

            $data = $query->pluck('value')->toArray();

            $metricValues[$metricName] = array_sum($data);

            $datasets[] = [
                'metric' => $metricName,
                'data' => $data,
                'prev_sum' => 0,
                'pct_change' => 0
            ];
        }

        $evaluator = new ComputedMetricEvaluator();

        foreach ($this->metrics as $metricName) {

            $metric = $this->registry->get($metricName);

            if (!$metric->isComputed()) {
                continue;
            }

            $value = $evaluator->evaluate(
                $metric->expressionValue(),
                $metricValues
            );

            $datasets[$metricName] = [
                'metric' => $metricName,
                'data' => [$value]
            ];
        }

        $series = [
            'labels' => [],
            'datasets' => $datasets
        ];

        return (new ChartBuilder())->build(
            $series,
            $this->metrics,
            []
        );
    }
}
