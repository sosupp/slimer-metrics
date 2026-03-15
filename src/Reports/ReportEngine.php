<?php
namespace Sosupp\SlimerMetrics\Reports;

use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Sosupp\SlimerMetrics\Charts\ChartAdapterInterface;
use Sosupp\SlimerMetrics\Contracts\MetricRegistryInterface;
use Sosupp\SlimerMetrics\Engine\ComputedMetricEvaluator;
use Sosupp\SlimerMetrics\Engine\MetricQueryBuilder;
use Sosupp\SlimerMetrics\Traits\WithDateFormat;

class ReportEngine
{
    use WithDateFormat;

    protected $registry;

    protected $metrics = [];

    protected $date;

    protected $groupBy;

    protected $adapter;

    protected $currency = 'GHc';

    protected $chartType = 'bar';

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
        $this->date = $this->configureDateAsRange($date);
        // dd($this->date);

        return $this;
    }

    public function groupBy($groupBy)
    {
        $this->groupBy = $groupBy;

        // dd($this->groupBy);

        return $this;
    }

    public function currency($currency = 'GHc')
    {
        $this->currency = $currency;

        return $this;
    }

    public function chartType($type = 'bar')
    {
        $this->chartType = $type;

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

        $driver = DB::getDriverName();
        [$dateExpr, $phpFormat, $periodInterval] = $this->dateGrouping($driver, $this->groupBy);

        $datasets = [];
        $metricValues = [];

        foreach ($this->metrics as $metricName) {
            $metric = $this->registry->get($metricName);

            // dd($metric->chartType);

            if ($metric->isComputed()) {
                continue;
            }

            $query = $builder->build($metricName, $this->date, $dateExpr);

            $data = $query->pluck('value')->toArray();

            // dd($data);

            $metricValues[$metricName] = array_sum($data);

            // dd($metricValues);

            $datasets[] = [
                'chartType' => $metric->chartType,
                'metric' => $metricName,
                'data' => $data,
                'prev_sum' => 0,
                'pct_change' => 0,
            ];

        }

        $evaluator = new ComputedMetricEvaluator();

        // dd($builder, $this->metrics);

        foreach ($this->metrics as $metricName) {

            $metric = $this->registry->get($metricName);

            // dd($metric);

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

        // dd($datasets);

        $from = Carbon::parse($this->date['start']);
        $to = Carbon::parse($this->date['end']);

        // dd($this->date, $from, $to);

        // dd($dateExpr, $phpFormat, $periodInterval, $datasets);

        $labels = $this->buildLabels($from, $to, $phpFormat, $this->groupBy, $periodInterval);

        $series = [
            'labels' => $labels,
            'datasets' => $datasets,
            'meta' => [
                'currency' => $this->currency,
            ]
        ];


        // dd($labels, $driver, $dateExpr, $series);

        $chart =  (new ChartBuilder())->build(
            $series,
            $this->metrics,
            $labels
        );

        // dd($query, $data, $datasets, $metricValues, $evaluator, $series, $chart, $this->metrics);
        return $chart;
    }

    protected function buildLabels(Carbon $from, Carbon $to, string $format, string $groupBy, string $interval): array
    {
        // Normalize period bounds to group starts
        $from = $this->floorToGroup($from->copy(), $groupBy);
        $to   = $this->ceilToGroup($to->copy(), $groupBy);

        $labels = [];
        foreach (CarbonPeriod::create($from, $interval, $to) as $date) {
            $labels[] = $this->formatGroupLabel($date, $groupBy, $format);
        }

        // Ensure last bound included
        $last = $this->formatGroupLabel($to, $groupBy, $format);
        if (!in_array($last, $labels, true)) {
            $labels[] = $last;
        }

        return $labels;
    }

    protected function floorToGroup(Carbon $date, string $groupBy): Carbon
    {
        return match ($groupBy) {
            'month' => $date->startOfMonth(),
            'week'  => $date->startOfWeek(), // ISO by default if set in config/app.php
            default => $date->startOfDay(),
        };
    }

    protected function ceilToGroup(Carbon $date, string $groupBy): Carbon
    {
        return match ($groupBy) {
            'month' => $date->endOfMonth(),
            'week'  => $date->endOfWeek(),
            default => $date->endOfDay(),
        };
    }

    protected function formatGroupLabel(Carbon $date, string $groupBy, string $format): string
    {
        if ($groupBy === 'week') {
            // For PHP week formatting with ISO week
            return $date->isoFormat('GGGG-[W]WW');
        }

        return $date->format($format);
    }

    protected function dateGrouping(string $driver, string $groupBy): array
    {
        $groupBy = strtolower($groupBy);

        // dd($groupBy);

        $map = match ($groupBy) {
            'month' => [
                'mysql'  => ["DATE_FORMAT(created_at, '%Y-%m')", 'Y-m', '1 month'],
                'pgsql'  => ["to_char(created_at, 'YYYY-MM')", 'Y-m', '1 month'],
                'sqlite' => ["strftime('%Y-%m', created_at)", 'Y-m', '1 month'],
                null  => ["DATE(created_at)", 'Y-m', '1 month'],
            ],

            'week' => [
                'mysql'  => ["DATE_FORMAT(created_at, '%x-W%v')", 'o-\WW', '1 week'], // ISO week
                'pgsql'  => ["to_char(created_at, 'IYYY-\"W\"IW')", 'o-\WW', '1 week'],
                'sqlite' => ["strftime('%Y-W%W', created_at)", 'Y-\WW', '1 week'],
                null  => ["DATE(created_at)", 'o-\WW', '1 week'],
            ],

            default /* day */ => [
                'mysql'  => ["DATE_FORMAT(created_at, '%Y-%m-%d')", 'Y-m-d', '1 day'],
                'pgsql'  => ["to_char(created_at, 'YYYY-MM-DD')", 'Y-m-d', '1 day'],
                'sqlite' => ["strftime('%Y-%m-%d', created_at)", 'Y-m-d', '1 day'],
                null  => ["DATE(created_at)", 'Y-m-d', '1 day'],
            ]
        };

        $driverMap = $map[$driver] ?? $map['default'];

        return $driverMap;
    }

}
